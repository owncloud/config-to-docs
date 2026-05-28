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
composer update
```

### Usage

Run the converter for each config sample file, or use the provided script `./ctd.sh`:

```bash
php convert.php config:convert-adoc \
  --input-file=../core/config/config.sample.php \
  --output-file=../docs/modules/admin_manual/pages/configuration/server/config_sample_php_parameters.adoc

php convert.php config:convert-adoc \
  --input-file=../core/config/config.apps.sample.php \
  --output-file=../docs/modules/admin_manual/pages/configuration/server/config_apps_sample_php_parameters.adoc
```

Use `php convert.php --help` for a full list of arguments and options.

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
