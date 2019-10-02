#!/bin/sh
#
# Cloud Hook: Reinstall Standard with Lightning Layout

site="$1"
target_env="$2"

drush @$site.$target_env site-install standard --account-pass=admin --yes
drush @$site.$target_env pm-enable lightning_landing_page lightning_dev --yes
