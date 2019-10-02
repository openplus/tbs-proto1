@lightning @lightning_workflow @api
Feature: Moderated content
  As a site administrator, I need to be able to manage moderation states for
  content.

  Background:
    Given page content:
      | title   | moderation_state | promote |
      | Alpha   | review           | 1       |
      | Beta    | published        | 1       |
      | Charlie | draft            | 0       |

  @03ebc3ee @orca_public
  Scenario: Publishing moderated content
    Given I am logged in as a user with the "access content overview, view any unpublished content, use editorial transition review, use editorial transition publish, create page content, edit any page content, create url aliases" permissions
    When I visit "/admin/content"
    And I click "Alpha"
    And I visit the edit form
    And I select "Published" from "moderation_state[0][state]"
    And I press "Save"
    And I visit "/user/logout"
    And I visit "/node"
    Then I should see the link "Alpha"

  @c0c17d43 @orca_public
  Scenario: Unpublishing moderated content
    Given I am logged in as a user with the "access content overview, use editorial transition publish, use editorial transition archive, create page content, edit any page content, create url aliases" permissions
    And I visit "/admin/content"
    And I click "Beta"
    And I visit the edit form
    And I select "Archived" from "moderation_state[0][state]"
    And I press "Save"
    And I visit "/user/logout"
    And I go to "/node"
    Then I should not see the link "Beta"

  @cead87f0 @orca_public
  Scenario: Filtering content by moderation state
    Given I am logged in as a user with the "access content overview" permission
    When I visit "/admin/content"
    And I select "In review" from "moderation_state"
    And I apply the exposed filters
    Then I should see the link "Alpha"
    But I should not see the link "Beta"
    And I should not see the link "Charlie"

  @6a1db3b1
  Scenario: Examining the moderation history of a piece of content
    Given I am logged in as an administrator
    When I visit "/admin/content"
    And I click "Charlie"
    And I visit the edit form
    And I select "In review" from "moderation_state[0][state]"
    And I press "Save"
    And I visit the edit form
    And I select "Published" from "moderation_state[0][state]"
    And I press "Save"
    And I click "History"
    Then I should see "Set to draft"
    And I should see "Set to review"
    And I should see "Set to published"
