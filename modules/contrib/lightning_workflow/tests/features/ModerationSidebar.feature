@lightning @lightning_workflow @api
Feature: A sidebar for moderating content

  @1d83813d @javascript @with-module:toolbar @with-module:moderation_sidebar
  Scenario: Moderating content using the sidebar
    Given I am logged in as a page_reviewer
    When I am viewing a page in the Draft state
    Then I should be able to transition to the Published state without leaving the page
