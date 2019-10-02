## 3.5.0
* Lightning Workflow is now compatible with Lighting Core 4.x
  (Drupal core 8.7.x).
* Fixed missing configure button for Lightning Scheduler module in list page.
  (Issue #3034047)
* Replaced entity_load() calls with entity load(). (Issue #3033637)
* Added missing description in Lightning Scheduler module in links.menu.yml
  file. (Issue #3034048)

## 3.4.0
* Changes were made to internal testing infrastructure, but nothing that will
  affect users of Lightning Workflow.

## 3.3.0
* Updated Lightning Core to 3.5, which security updates Drupal core to 8.6.6.
* Made the time steps in Lightning Scheduler's UI configurable. (Issue #2981050)
* Fixed a bug in the Moderation History where the users and timestamps didn't
  correctly correspond to the actual revisions. (Issue #3022898)
* Updated Moderation Dashboard to its latest stable version.
* Refactored underlying scheduler UI code to be less sensitive to time zones.
* Added project namespaces to all stated dependencies. (Issue #2999322)
* Changes were made to the internal testing infrastructure, but nothing that
  will affect users of Lightning Workflow.

## 3.2.0
* Many changes to internal testing code and infrastructure, but nothing that
  affects users of Lightning Workflow.

## 3.1.0
* Lightning Workflow now includes the Moderation Dashboard module. (#70)

## 3.0.0
* Updated Lightning Core to 3.0, which requires Drupal core 8.6.0.
* The Editorial workflow has been added to this module as it is no longer
  provided by Content Moderation. (CR #2958726)
* Fixed a bug where Lightning Scheduler would keep publishing content.
  (Issue #2981817)
* Added a return value to hook_requirements implementation. (Issue #2984734)

## 2.4.0
* Fixed an incompatibility between Lightning Scheduler and Inline Entity Form
  (Issue #2993137)

## 2.3.0
* Moderation Sidebar is now included with Lightning Workflow. (#62)
* The editorial workflow transitions have changed. "Review" is now "Send to
  review", "Restore" is now "Restore from archive", and the "Restore to Draft"
  transition has been merged into the "Create new draft" transition. (#64)

## 2.2.0
* Fixed a bug where Lightning Scheduler would not correctly handle "AM/PM" in
  its input fields. (Issue #2981807)
* Fixed a fatal error caused by old code that was not removed during the shift
  to Content Moderation. (Issue #2973811)
* Renamed Lightning Scheduler's ECMAScript 6 files to .es6.js, and provided a
  source map to help community developers debug and file patches. (#50, #51)

## 2.0.0
* Scheduler UI is now compatible with Internet Explorer.
* Remove duplicate step definitions.

## 2.0.0-rc2
* Lightning Scheduler now clears cached state data (site state, not
  moderation states) during cron runs, in order to fix a bug where
  scheduled transitions in the past would not display correctly. (GitHub #30)

## 2.0.0-rc1
* Lightning Scheduler has been completely rewritten and has a new UI. Users
  with permission to schedule various workflow state transitions will be able
  to schedule transitions to take place at any date and time they want. They
  can also schedule several transitions at once. Transition data is now stored
  in fields called scheduled_transition_date and scheduled_transition_state,
  which replace the old scheduled_moderation_state and scheduled_publication
  fields. A UI is also provided so you can migrate scheduled transition data
  from the old fields into the new ones. You will see a link to this UI once
  you complete the update path. (Issues #2935715, #2935198, #2935105, #2936757, #2954329, and #2954348)

## 1.2.0
* If you have Lightning Roles
  (part of [Lightning Core](https://drupal.org/project/lightning_core))
  installed, the "reviewer" roles will now receive permission to view
  unpublished content and revisions.

## 1.1.0
* Behat contexts used for testing were moved into the
  `Acquia\LightningExtension\Context` namespace.

## 1.0.0
* No changes since last release.

## 1.0.0-rc2
* Remove legacy update code.

## 1.0.0-rc1
* Loosen the tight coupling between Lightning Workflow and Views.
  (Issue #2938769)
