@lightning @api @lightning_workflow
Feature: Integration of workflows with Quick Edit

  @f2beeeda @javascript @with-module:quickedit @orca_public
  Scenario: Quick Edit should be available for unpublished content
    Given I am logged in as a user with the "access in-place editing, access contextual links, use editorial transition create_new_draft, view any unpublished content, edit any page content" permissions
    And page content:
      | title  | path    | moderation_state |
      | Foobar | /foobar | draft            |
    When I visit "/foobar"
    Then Quick Edit should be enabled
