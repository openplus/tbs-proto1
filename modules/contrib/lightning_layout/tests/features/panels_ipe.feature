@lightning @lightning_layout @api
Feature: Creating and editing landing pages visually

  @javascript @43f95224 @orca_public
  Scenario: One-off changes can be made to Landing Pages using the IPE out of the box.
    Given I am logged in as a landing_page_creator
    And landing_page content:
      | title  | path    |
      | Foobar | /foobar |
    When I visit "/foobar"
    And I place the "views_block:who_s_online-who_s_online_block" block from the "Lists (Views)" category
    And I save the layout
    And I visit "/foobar"
    Then I should see a "views_block:who_s_online-who_s_online_block" block

  @javascript @ccabe17e
  Scenario: Changing layouts through the IPE
    Given I am logged in as a landing_page_creator
    And landing_page content:
      | title  | path    |
      | Foobar | /foobar |
    When I visit "/foobar"
    And I change the layout to "layout_threecol_25_50_25" from the "Columns: 3" category
    Then I should see "Region: first"
    And I should see "Region: second"
    And I should see "Region: third"
    When I change the layout to "layout_twocol" from the "Columns: 2" category
    Then I should see "Region: first"
    And I should see "Region: second"
    And I should not see "Region: third"
