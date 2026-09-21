#!/bin/bash
#
# If not already made, make this script executable by running sudo chmod +x ctd.sh

printf "\nConfig-To-Docs update script\n"
printf "\nThis script assumes that you have the git clone directory of core and docs on the same file hierarchy level as you have the cloned config-to-docs clone from where this script runs !!\n"
printf "\nPlease ensure before running this script, that you have switched to the correct branch in core AND you have created and switched to a pushable target branch in docs !!\n\n"

# Override either if your clones are not siblings of this one, e.g.
#   CORE=../owncloud/core DOCS=../docs.owncloud.com ./ctd.sh
CORE="${CORE:-../core}"
DOCS="${DOCS:-../docs.owncloud.com}"

if [ ! -d "$CORE" ]; then
  printf "\ncore repo not found at %s - exiting\n" "$CORE"
  exit 1
fi

if [ ! -d "$DOCS" ]; then
  printf "\ndocs repo not found at %s - exiting\n" "$DOCS"
  exit 1
fi

# The docs repo keeps one directory per server version, so the output path
# depends on which branch core is on. Derive it from the core checkout rather
# than asking, so the core branch and the docs version cannot silently disagree.
# shellcheck disable=SC2016  # the $ belongs to the PHP variable name being matched
VERSION=$(sed -n 's/^$OC_VersionString = .\([0-9]*\.[0-9]*\).*/\1/p' "$CORE/version.php")

if [ -z "$VERSION" ]; then
  printf "\ncould not read the server version from %s/version.php - exiting\n" "$CORE"
  exit 1
fi

PAGES="$DOCS/content/server/$VERSION/modules/admin_manual/pages/configuration/server"

if [ ! -d "$PAGES" ]; then
  printf "\ndocs pages for server %s not found at %s - exiting\n" "$VERSION" "$PAGES"
  printf "is core on the branch you meant, and does docs have that version?\n"
  exit 1
fi

# convert.php exits 0 on both of its error paths - unreadable input, unwritable
# output - and it refuses to create a missing output file. So check both ends
# here, or a typo'd path prints DONE and changes nothing.
for sample in config.sample.php config.apps.sample.php; do
  if [ ! -r "$CORE/config/$sample" ]; then
    printf "\ncore sample %s is missing or not readable - exiting\n" "$CORE/config/$sample"
    exit 1
  fi
done

for page in config_sample_php_parameters.adoc config_apps_sample_php_parameters.adoc; do
  if [ ! -w "$PAGES/$page" ]; then
    printf "\ntarget page %s is missing or not writable - exiting\n" "$PAGES/$page"
    exit 1
  fi
done

printf "\nExporting core %s into %s\n" "$VERSION" "$PAGES"

# mandatory prompt for starting the export
read -p "Do you want to start exporting changes (y/N)? " -r -e answer
(echo "$answer" | grep -iq "^y") && changes="y" || changes="n"

if [ ! "$changes" = "y" ]; then
  # declining is a successful no-op, not a failure
  exit 0
fi

printf "\nExporting: config.sample.php\n"
php convert.php config:convert-adoc \
  --input-file="$CORE/config/config.sample.php" \
  --output-file="$PAGES/config_sample_php_parameters.adoc"
printf "DONE\n"

printf "\nExporting: config.apps.sample.php\n"
php convert.php config:convert-adoc \
  --input-file="$CORE/config/config.apps.sample.php" \
  --output-file="$PAGES/config_apps_sample_php_parameters.adoc"
printf "DONE\n\n"

printf "\nPlease go now to docs and check/push the changes made\n\n"
