# ownCloud config sample to AsciiDoc converter

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/c5e375e4b4af47238dcb8fc234960da4)](https://www.codacy.com/app/settermjd/ownCloud-config-converter?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=settermjd/ownCloud-config-converter&amp;utm_campaign=Badge_Grade)

Both, `config.sample.php` and `config.apps.sample.php` have their home in core and are written as pure php samples. To use these samples for the owncloud documentation, you need to convert them to an `.adoc` file usable in docs. This repo supports the conversion to an .adoc file by providing an automatism for this  process. 

**Note:** It is necessary to run this command always for both files `config.sample.php` and `config.apps.sampe.php`

**Note:** You will find the latest version of the source files to convert in https://github.com/owncloud/core/tree/master/config 

## Requirements

Install the dependencies with `composer`:

	composer update

## Rules

Currently this relies on the following rules

 * The main files are always in core. Any changes must be done there. Config sample changes made in docs will be overwritten by the next `config-to-docs` run
 * On any changes made to a config sample file in core, you *MUST* run `make test-php-style-fix` to check if changes made fulfill the php requirements 
 * Each config sample file in core must start with `<?php`, a first comment block describing general rules for this file embedded in `/**` ` */` and the main array `$CONFIG = [];`
 * All configs have to be written inside the brackets of `$CONFIG = [];`
 * If not already present in docs, you have to have for each config file one with a predefined header content. This content is reused as descriptive content on each run and is not replaced by the conversion process. The header follows the asciidoc style and is a necessary part of the rendering process
 * All text added by the conversion process will be added *below* the `// header end do not delete or edit this line` line. Existing content will always be replaced.
 * Changes to the header can be part of a `config-to-docs` run, but mast be made manually.
 * Each config section must have exact one descriptive comment block for the config and directly following at minimum one or more comment blocks describing the key/value and a uncommented line with the key/value itself. `key/value` must follow php syntax.
 * If you have multiple keys for the same comment section, separate them with a blank line, which is necessary for the documentation generation process.
 * The first text line in the first comment block of a config section is used as text for the table of contents. Write brief and carefully
 * Use RST syntax in comment lines
 * Use examples of the current config files to add new ones.

## General Command

The command has the following structure.

```
convert.php [options] [arguments]
```

For a detailed list of arguments and options, use `--help`.

## How to use

To ease the conversion process, the following steps / prerequisites should be taken:

 * You have cloned `core`, `docs` and the `config-to-docs` repo locally, into the same base directory
 * `core`: the master branch should be checked out, containing the latest merged sample files
 * `docs`: you have created a new branch based on an updated master, this branch will contain all changes that will be pushed to docs
 * `config-to-docs`: you have changed into the root of this directory and are ready to run the commands

Use the following commands for each sample file, or the prepared script `./ctd.sh` which runs both commands below and reminds you about the prerequisites.

```
php convert.php config:convert-adoc \
  --input-file=../core/config/config.sample.php \
  --output-file=../docs/modules/admin_manual/pages/configuration/server/config_sample_php_parameters.adoc

php convert.php config:convert-adoc \
  --input-file=../core/config/config.apps.sample.php \
  --output-file=../docs/modules/admin_manual/pages/configuration/server/config_apps_sample_php_parameters.adoc
```

## Advice

When doing changes in the core config sample files or when changes have been already made, regulary do a `config-to-docs` run to check the result. Check the result of the converted file in docs via the browser having an asciidoc previewer enabled. When satisfied, mandatory run in core `make test-php-style-fix`, fix any problems that might raise and recheck the result in the browser. Changes made in core must be merged *before* changes are pushed to docs. When final, push the changes to docs and create a PR.

## License

The MIT License (MIT)

Copyright (c) 2014 Morris Jobke <hey@morrisjobke.de>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
