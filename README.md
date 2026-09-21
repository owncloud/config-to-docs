# ownCloud Config to Docs Converter

<!-- OSPO-managed README | Generated: 2026-04-16 | v2 -->

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE) [![ownCloud OSPO](https://img.shields.io/badge/OSPO-ownCloud-blue)](https://kiteworks.com/opensource)

This tool converts ownCloud Server's `config.sample.php` and `config.apps.sample.php` files into AsciiDoc format for use in the ownCloud documentation. It parses PHP configuration samples, extracts comments and key/value pairs, and generates structured `.adoc` files that integrate with the documentation build pipeline. The converter ensures that configuration documentation stays in sync with the source files in core.

## Part of Documentation

This repository supports the [ownCloud documentation](https://doc.owncloud.com) by automating the conversion of configuration samples from [ownCloud Server (Classic)](https://github.com/owncloud/core). The source PHP config files live in core, and this tool generates the AsciiDoc output used in the admin manual.

## Getting Started

Follow the steps below to set up and run the configuration converter.

### Requirements

Install dependencies with Composer:

```bash
composer install
```

Use `install`, not `update`. The comment parsing this tool depends on lives in
`phpdocumentor/reflection-docblock`, which `composer.json` accepts as `^4.3.0` while
`composer.lock` pins a known-good version. Floating it can silently change the generated
output.

### Usage

The docs repository keeps one directory per server version, and the output path depends on
which branch `core` is on. `./ctd.sh` derives that for you from `core/version.php`, so
prefer it:

```bash
./ctd.sh
```

To run a single conversion by hand, note that `--output-file` must already exist — the
converter reads the existing file to preserve its hand-written header (see
[Authoring rules](#authoring-rules)):

```bash
php convert.php config:convert-adoc \
  --input-file=../core/config/config.sample.php \
  --output-file=../docs.owncloud.com/content/server/11.0/modules/admin_manual/pages/configuration/server/config_sample_php_parameters.adoc

php convert.php config:convert-adoc \
  --input-file=../core/config/config.apps.sample.php \
  --output-file=../docs.owncloud.com/content/server/11.0/modules/admin_manual/pages/configuration/server/config_apps_sample_php_parameters.adoc
```

Use `php convert.php --help` for a full list of arguments and options.

## Authoring rules

`config.sample.php` and `config.apps.sample.php` in core tell contributors that changes
"must follow the rules documented in the readme of the `config-to-docs` repository". These
are those rules. They are not style preferences — each one describes output this converter
will get wrong or silently drop.

### How a docblock becomes AsciiDoc

Everything between `$CONFIG = [` and `];` is split on `/**`. Each docblock becomes one
section, and the PHP code following it (if any) becomes that section's code sample.

- **The first line is the heading.** It ends at the first `.` at end-of-line, or the first
  blank line, whichever comes first. A heading that unexpectedly swallows its second line
  usually means the first line lacks a trailing period. Avoid `..` and `...` in it: two
  consecutive dots end the heading on the spot, mid-line.
- **A docblock with no code after it** produces a level-2 heading (`== ...`); **with code
  after it**, a level-3 heading plus a `==== Code Sample` block.
- **Everything after the heading is emitted as raw AsciiDoc.** That is why the sample files
  contain literal AsciiDoc — labeled lists (`keyname::`), backtick spans, `**bold**`, link
  macros. Write valid AsciiDoc there, and it appears verbatim.

The heading and the body go through different filters, so several rules below apply to one
and not the other. Where that matters it is called out.

### What survives

- Blank ` *` lines become blank lines, so multiple paragraphs work.
- A lone ` * +` line survives, so AsciiDoc list continuations work.
- Link macros survive intact, including `?`, `&` and `#` in the URL.
- Backticks and `%` are safe anywhere, including in a heading.
- `**bold**` is safe in the body and mid-heading.
- Flush-left `-` and `*` lists are untouched.

### What breaks

- **Never start a line with `@` followed by a letter.** It is parsed as a phpDoc tag, and
  everything from there to the end of the docblock is dropped from the output without
  warning. Indenting it does not help. If it is the first line you get an empty heading.
- **Never put a ` */` inside the code that follows a docblock** — an inline comment such as
  `'key' => 'value', /* note */` is enough. The block is split on ` */`, so the whole code
  sample is dropped and the heading silently demotes from level 3 to level 2. A literal
  `/**` inside a sample value invents a bogus extra section.
- **Never begin a line with a single character followed by `-`.** A filter deletes that
  leading run, so a line wrapped onto `e-mail to the admin` is published as
  `-mail to the admin`. Mid-line is fine — `send an e-mail` inside a line is untouched.
  The filter exists to pull indented `-` list items flush left.
- **Never follow a line consisting of one character with a line starting `-`.** The filter
  spans whitespace *including newlines*, so the character and the line break are both lost —
  and a blank line between them does not protect you, it is consumed too.
- **Do not start a heading with `*`.** The leading asterisk is stripped, so a heading opening
  `**bold**` publishes as `*bold**`.
- **Keep `'`, `"`, `<`, `>` and `&` out of the first line.** A level-2 heading is
  HTML-escaped with `ENT_QUOTES`, so an apostrophe becomes `&#039;` — by far the most likely
  way to hit this. Level-3 headings are *not* escaped, which is why `ownCloud's` survives in
  today's pages: those blocks happen to have code samples. Do not rely on that.
- **Avoid double backticks and tabs in the body** — they collapse to one backtick and to a
  single space. In a heading both survive, and tabs inside a code sample survive too.
- **Avoid `{}` and `{@}` in the body.** They are the docblock library's escape sequences and
  come out as `}` and `@`. Headings are unaffected. Inline tags such as `{@link ...}` are
  re-parsed and re-rendered, so they are not guaranteed to survive byte-identically.
- **Indentation.** Do not rely on it in either direction. The first body line always loses
  its own indentation, and is also excluded from the calculation that dedents the rest, so
  the lines after it keep indentation only when some later non-blank line is flush left.
  Where indentation does survive, an indented line following a blank line becomes an
  AsciiDoc literal block.

### Two rewrites to know about

- `.. warning::` — a leftover from the reStructuredText era — is rewritten to `WARNING: `,
  which AsciiDoc renders as an admonition. Beware that the pattern is neither anchored to the
  start of a line nor escaped, so *any* two characters followed by ` warning::` match
  anywhere in a line. Neither core sample still uses it; both write `WARNING:` directly.
- In a code sample, four leading spaces are stripped from each line — except the first, which
  is left-trimmed completely. Start every sample flush at four spaces, as the existing entries
  do, and the two agree; indent the first line differently and the sample's relative
  indentation is destroyed.

### The output file's header

Each generated page begins with a hand-written introduction ending in:

```
// header end do not delete or edit this line
```

The converter reads the existing output file, keeps everything **above** that line, and
replaces everything **below** it. So the introduction is edited in the docs repository, and
everything else is edited in core — a change made below the marker is lost on the next run.

Two consequences of how that is matched: if the marker line is deleted, the introduction is
silently replaced by a bare marker; if it appears twice, everything up to the **last** one is
kept.

## Documentation

- [ownCloud Server documentation](https://doc.owncloud.com)
- See the source config files in [owncloud/core/config](https://github.com/owncloud/core/tree/master/config)

## Community & Support

**[Star](https://github.com/owncloud/config-to-docs)** this repo and **Watch** for release notifications!

- [ownCloud Website](https://owncloud.com)
- [Community Discussions](https://github.com/orgs/owncloud/discussions)
- [Matrix Chat](https://app.element.io/#/room/#owncloud:matrix.org)
- [Documentation](https://doc.owncloud.com)
- [Enterprise Support](https://owncloud.com/contact-us/)
- [OSPO Home](https://kiteworks.com/opensource)

## Contributing

We welcome contributions! Please read the [Contributing Guidelines](CONTRIBUTING.md)
and our [Code of Conduct](CODE_OF_CONDUCT.md) before getting started.

### Workflow

- **Rebase Early, Rebase Often!** We use a rebase workflow. Always rebase on the target branch before submitting a PR.
- **Dependabot**: Automated dependency updates are managed via Dependabot. Review and merge dependency PRs promptly.
- **Signed Commits**: All commits **must** be PGP/GPG signed. See [GitHub's signing guide](https://docs.github.com/en/authentication/managing-commit-signature-verification).
- **DCO Sign-off**: Every commit must carry a `Signed-off-by` line:
  ```
  git commit -s -S -m "your commit message"
  ```
- **GitHub Actions Policy**: Workflows may only use actions that are (a) owned by `owncloud`, (b) created by GitHub (`actions/*`), or (c) verified in the GitHub Marketplace.

## Security

**Do not open a public GitHub issue for security vulnerabilities.**

Report vulnerabilities at **<https://security.owncloud.com>** -- see [SECURITY.md](SECURITY.md).

Bug bounty: [YesWeHack ownCloud Program](https://yeswehack.com/programs/owncloud-bug-bounty-program)

## License

This project is licensed under the [MIT](LICENSE).

## About the ownCloud OSPO

The [Kiteworks Open Source Program Office](https://kiteworks.com/opensource), operating under
the [ownCloud](https://owncloud.com) brand, launched on May 5, 2026, to steward the open source
ecosystem around ownCloud's products. The OSPO ensures transparent governance, license compliance,
community health, and sustainable collaboration between the open source community and
[Kiteworks](https://www.kiteworks.com), which acquired ownCloud in 2023.

- **OSPO Home**: <https://kiteworks.com/opensource>
- **GitHub**: <https://github.com/owncloud>
- **ownCloud**: <https://owncloud.com>

For questions about the OSPO or licensing, contact ospo@kiteworks.com.

### License Migration to Apache 2.0

The OSPO is driving a strategic relicensing of ownCloud repositories toward the
[Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0), following
the [Apache Software Foundation's third-party license policy](https://www.apache.org/legal/resolved.html).

Individual repositories will migrate as their audit is completed. The LICENSE file
in each repo reflects its **current** license status (not the target).

**Current license: MIT** (Category A per Apache policy -- permissive, compatible with Apache-2.0).

Migration prerequisites for this repository:

- **CLA/DCO coverage**: All past contributors must have signed agreements permitting relicensing
- **Header updates**: All source file headers must be updated from MIT to Apache-2.0 notice
- **Dependency audit**: Verify no incompatible transitive dependencies
