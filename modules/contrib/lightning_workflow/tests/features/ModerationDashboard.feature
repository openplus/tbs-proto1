@lightning @lightning_workflow @api
Feature: Moderation Dashboard

  @ea966cba @with-module:moderation_dashboard
  Scenario: Administrators can use the dashboard
    Given I am logged in as an administrator
    When I click "Moderation Dashboard"
    Then I should see a dashboard for moderating content
