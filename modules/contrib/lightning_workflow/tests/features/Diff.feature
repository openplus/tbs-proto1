@lightning @lightning_workflow @api
Feature: Diffing different revisions of content

  @5b4ba63e @with-module:diff
  Scenario: Diffing two node revisions
    Given I am logged in as an administrator
    And page content:
      | title       | body           | moderation_state |
      | Pastafazoul | First revision | draft            |
    When I visit "/admin/content"
    And I click "Pastafazoul"
    And I visit the edit form
    And I enter "Second revision" for "body[0][value]"
    And I press "Save"
    And I visit the edit form
    And I enter "Third revision" for "body[0][value]"
    And I press "Save"
    And I compare the 1st and 2nd revisions
    Then I should see "Changes to Pastafazoul"
