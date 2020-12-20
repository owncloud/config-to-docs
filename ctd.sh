#!/bin/bash
#
# If not already made, make this script executable by running sudo chmod +x ctd.sh

printf "\nConfig-To-Docs update script\n"
printf "\nThis script assumes that you have the git clone directory of core and docs on the same file hierarchy level as you have the cloned config-to-docs clone from where this script runs !!\n"
printf "\nPlease ensure before running this script, that you have switched to the correct branch in core AND you have created and switched to a pushable target branch in docs !!\n\n"

CORE="../core"
DOCS="../docs"

if [ ! -d "$CORE" ]; then
  printf "\ncore repro not found - exiting\n"
  exit
fi

if [ ! -d "$DOCS" ]; then
  printf "\ndocs repro not found - exiting\n"
  exit
fi

# mandatory prompt for starting the export
read -p "Do you want to start exporting changes (y/N)? " -r -e answer
(echo "$answer" | grep -iq "^y") && changes="y" || changes="n"

if [ ! "$changes" = "y" ]; then
  exit
fi

printf "\nExporting: config.sample.php\n"
php convert.php config:convert-adoc \
  --input-file=$CORE/config/config.sample.php \
  --output-file=$DOCS/modules/admin_manual/pages/configuration/server/config_sample_php_parameters.adoc
printf "DONE\n"

printf "\nExporting: config.apps.sample.php\n"
php convert.php config:convert-adoc \
  --input-file=$CORE/config/config.apps.sample.php \
  --output-file=$DOCS/modules/admin_manual/pages/configuration/server/config_apps_sample_php_parameters.adoc
printf "DONE\n\n"

printf "\nPlease go now to docs and check/push the changes made\n\n"
