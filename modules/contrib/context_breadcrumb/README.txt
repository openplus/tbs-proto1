CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Recommended Modules
* Installation
* Configuration
* Maintainers


INTRODUCTION
------------

Integration Breadcrumb module with Context. This module allow dynamic
define custom breadcrumb for Drupal site.

 * For a full description of the module visit:
  https://www.drupal.org/project/context_breadcrumb

 * To submit bug reports and feature suggestions, or to track changes visit:
  https://www.drupal.org/project/issues/context_breadcrumb


REQUIREMENTS
------------

This module requires modules:
 * Context: https://www.drupal.org/project/context
 * Token: https://www.drupal.org/project/token


RECOMMENDED MODULES
-------------------

 * Ctools: https://www.drupal.org/project/ctools
   Add more condition for Context.

 * Term condition: https://www.drupal.org/project/term_condition
   Add Taxonomy term condition for context.


INSTALLATION
------------

Install the context_breadcrumb module as you would normally install a
contributed Drupal module. Visit https://www.drupal.org/node/1897420
for further information.


CONFIGURATION
--------------

    1. Navigate to Administration > Extend and enable the Custom Permissions
       Context module.
    2. Navigate to Administration >  Structure > Context.
    3. Add new context and "Add condition".
    4. "Add reaction" with "Breadcrumb".
    5. Add breadcrumb for your context.


MAINTAINERS
-----------

The 8.x-1.x branch was created by:

 * Thao Huyn Khac (zipme_hkt) - https://www.drupal.org/u/zipme_hkt
