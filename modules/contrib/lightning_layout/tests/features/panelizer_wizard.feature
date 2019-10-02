@lightning @lightning_layout @api
Feature: Panelizer wizard

  @javascript @7917f3ad
  Scenario: Switch between defined layouts.
    Given I am logged in as a user with the "landing_page_creator, layout_manager" roles
    And I visit "/admin/structure/panelizer/edit/node__landing_page__full__two_column/content"
    And I place the "Authored by" block into the first panelizer region
    And I press "Update and save"
    And landing_page content:
      | title  | path    |
      | Foobar | /foobar |
    When I visit "/foobar"
    And I visit the edit form
    And I select "Two Column" from "Full content"
    And press "Save"
    Then I should see "Authored by"
    And I visit the edit form
    And I select "Single Column" from "Full content"
    And press "Save"
    And I should not see "Authored by"
    And I visit "/admin/structure/panelizer/edit/node__landing_page__full__two_column/content"
    And I remove the "Authored by" block from the first panelizer region

  @javascript @20e106df @orca_public
  Scenario: Create a new layout using the Panelizer wizard
    Given I am logged in as a user with the "administer panelizer, administer panelizer node landing_page defaults, administer node display" permissions
    When I go to "/admin/structure/panelizer/add/node/landing_page/full"
    And I press "Next"
    And I enter "Foo" for "Wizard name"
    And I enter "foo" for "Machine-readable name"
    And I press "Next"
    And I press "Next"
    And I press "Next"
    And I enter "[node:title]" for "Page title"
    And I place the "Authored by" block into the "content" panelizer region
    And I press "Finish"
    And I press "Cancel"
    And I should be on "/admin/structure/types/manage/landing_page/display/full"
    Then I should see "Foo"
    # Clean up.
    And I go to "/admin/structure/panelizer/delete/node__landing_page__full__foo"
    And I press "Confirm"
