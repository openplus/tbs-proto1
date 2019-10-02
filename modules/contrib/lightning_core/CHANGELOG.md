## 3.9.0
* Security updated Drupal core to 8.6.13 (SA-CORE-2019-004).

## 3.8.0
* Updated Drupal core to 8.6.11.
* Removed deprecated function calls. (Issue #3034195)

## 3.7.0
* Security updated Drupal core to 8.6.10 (SA-CORE-2019-003).
* Security updated Metatag to 1.8 (SA-CONTRIB-2019-021).

## 3.6.0
* Lightning Core now supports attaching pictures to user accounts, and includes
  a Compact display which displays the user's picture and name, both optionally
  linked to the user's profile. (Issue #3026959)
* Lightning Core now includes a "Long (12-hour)" date format, which formats
  dates and times like "April 1, 2019 at 4:20 PM".
* Fixed a bug where Lightning's utility to convert descendant profiles to the
  Drupal 8.6-compatible format would fail if the active profile was itself a
  descendant profile. (Issue #2997990)
* Fixed an "undefined index" bug that could happen when processing form
  elements which can have legends. (Issue #3018499)
* Namespaced all declared dependencies. (Issue #2995711)

## 3.5.0
* Security updated Drupal core to 8.6.6.
* Lightning Core will now automatically clear all persistent caches _before_
  running database updates with Drush 9.

## 3.4.0
* Updated Drupal core to 8.6.4.

## 3.3.0
* Updated Drupal core to 8.6.3.
* Various improvements to testing infrastucture.

## 3.2.0
* Security updated Drupal core to 8.6.2.
* Updated Pathauto to version 1.3. (#86)

## 3.1.0
* Updated Drupal core to 8.6.1.

## 3.0.0
* Updated Drupal core to 8.6.0.
* Removed the 'partial_matches' configuration from the Search API database
  backend bundled with Lightning Search.
* If Pathauto is installed, the Basic Page content type will automatically
  generate URL aliases. (#74)
* Fixed a bug where the Basic Page content type could fail to have workflow
  enabled when it should be. (Issue #2990048)
* Fixed a bug where Lightning-generated user roles had a null is_admin value.
  (Issue #2882197)

## 2.8.0
* Fixed a bug where user 1 could not access Lightning's administrative screens.
  (Issue #2933520)

## 2.7.0
* Updated Drupal core to 8.5.4.
* Drush updb failure from drush_lightning_core_pre_updatedb (Issue #2972217)
* Tests: "When I visit" step definition is too general (Issue #2955092)

## 2.6.0
* Added a Drush 9 command hook which will clear all cached plugin definitions before
  database updates begin. (GitHub #55)

## 2.5.0
* Security updated Drupal core to 8.5.3.

## 2.4.0
* Security updated Drupal core to 8.5.2.

## 2.3.0
* Fixed an incompatibility with Search API which would cause fatal errors under
  certain circumstances. (Issue #2961547 and GitHub #46)
* The Basic page content type provided by Lightning Page will now be moderated
  only if and when Content Moderation is installed. (GitHub #40)
* Lightning Core is now compatible with Drupal Extension 3.4 or later only.
  (GitHub #43 and #44)

## 2.2.0
* Security updated Drupal core to 8.5.1. (SA-2018-002)
* When renaming the configuration which stores extension's version numbers,
  Lightning Core will no longer assume configuration by the same name does not
  already exist. (Issue #2955072)

## 2.1.0
* Behat contexts used for testing were moved into the
  `Acquia\LightningExtension\Context` namespace.

## 2.0.0
* Updated Drupal core to 8.5.x.

## 1.0.0-rc3
* Fixed a problem in the 8006 update that caused problems for users that had an
  existing `lightning.versions` config object.

## 1.0.0-rc2
* Behat contexts used for testing have been moved into Lightning Core.
* The `lightning.versions` config object is now `lightning_core.versions`.

## 1.0.0-rc1
* The `update:lightning` command can now be run using either Drupal Console or
  Drush 9.
* Component version numbers are now recorded on install (and via an update hook
  on existing installations) so that the `version` argument is no longer needed
  with the `update:lightning` command.

## 1.0.0-alpha3
* Updated core to 8.4.4.
