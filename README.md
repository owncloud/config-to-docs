# ownCloud config.(app).sample.php to AsciiDoc / RST converter

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/c5e375e4b4af47238dcb8fc234960da4)](https://www.codacy.com/app/settermjd/ownCloud-config-converter?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=settermjd/ownCloud-config-converter&amp;utm_campaign=Badge_Grade)

This script creates, depending of the command used, an `.adoc` or `.rst` file from the comments inside of
`config.sample.php` or `config.apps.sampe.php`.

**Note:** It is necessary to run this command alsways for both files `config.sample.php` and `config.apps.sampe.php`

**Note:** You will find the latest version of the source files to convert in https://github.com/owncloud/core/tree/master/config 

## Requirements

Install the dependencies with `composer`:

	composer update

## How to use

The command has the following structure.

```
convert.php [options] [arguments]
```

For a detailed list of arguments and options, use `--help`.

**Note:** When the conversation is done, copy or move the files created to the docs repository and create a pull request.

The following example commands assume you have cloned `core` and `docs`.
**Note:** Create and activate upfront a local docs branch which eases creating a pull request.

```
php convert.php config:convert-adoc \
  --input-file=../core/config/config.sample.php \
  --output-file=../docs/modules/administration_manual/pages/configuration/server/config_sample_php_parameters.adoc

php convert.php config:convert-adoc \
  --input-file=../core/config/config.apps.sample.php \
  --output-file=../docs/modules/administration_manual/pages/configuration/server/config_apps_sample_php_parameters.adoc
```

## Supported feature set

Currently this relies on following

 * all comments need to start with `/**` and end with ` */` - each on their own line
 * add a `@see CONFIG_INDEX` to copy a previously described config option also to this line
 * everything between the ` */` and the next `/**` will be treated as the config option

## Options to set

You can set following options:

The tag which invokes to copy a config description to the current position

	$COPY_TAG = 'see';

The file which should be parsed

	$CONFIG_SAMPLE_FILE = 'config/config.sample.php';

The file to put output in

	$OUTPUT_FILE = 'sample_config.rst';

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
