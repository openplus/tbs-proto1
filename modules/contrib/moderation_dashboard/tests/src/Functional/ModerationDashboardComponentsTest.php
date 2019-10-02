<?php

namespace Drupal\Tests\moderation_dashboard\Functional;

/**
 * Tests moderation dashboard components.
 *
 * @group moderation_dashboard
 */
class ModerationDashboardComponentsTest extends ModerationDashboardTestBase {

  /**
   * Elements to test.
   *
   * @var array
   *   An array of text asserts keyed by the CSS selector of the element to
   *   assert.
   *   - contains: array of strings which have to be found.
   *   - not_contains: array of strings shouldn't be found.
   */
  protected $moderationElements = [
    // Content in review.
    '.view-id-content_moderation_dashboard_in_review.view-display-id-block_1' => [],
    // Content drafts.
    '.view-id-content_moderation_dashboard_in_review.view-display-id-block_2' => [
      'contains' => ['Draft node'],
      'not_contains' => ['Published node'],
    ],
    // Editor activity for the last 30 days.
    '.block-moderation-dashboard-activity' => [],
    // Recent updates.
    '.view-id-moderation_dashboard_recent_changes.view-display-id-block_1' => [
      'contains' => ['Published node', 'Draft node'],
    ],
    // Recently created.
    '.view-id-moderation_dashboard_recently_created.view-display-id-block_1' => [
      'contains' => ['Published node', 'Draft node'],
    ],
    // Your drafts.
    '.view-id-content_moderation_dashboard_in_review.view-display-id-block_3' => [
      'contains' => ['Draft node'],
      'not_contains' => ['Published node'],
    ],
    // Your activity.
    '.view-id-moderation_dashboard_recent_changes.view-display-id-block_2' => [
      'contains' => ['Published node', 'Draft node'],
    ],
    // Content you created.
    '.view-id-moderation_dashboard_recently_created.view-display-id-block_2' => [
      'contains' => ['Published node', 'Draft node'],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalLogin($this->user);
  }

  /**
   * Tests that blocks and other elements exist on the user dashboard.
   */
  public function testModerationElement() {
    $this->drupalCreateNode([
      'title' => 'Draft node',
      'moderation_state' => 'draft',
    ]);

    $this->drupalCreateNode([
      'title' => 'Published node',
      'moderation_state' => 'published',
    ]);

    $this->drupalGet('/user/' . $this->user->id() . '/moderation/dashboard');

    foreach ($this->moderationElements as $selector => $asserts) {
      $contains = !empty($asserts['contains']) ? $asserts['contains'] : [];
      $not_contains = !empty($asserts['not_contains']) ? $asserts['not_contains'] : [];
      $moderation_element = $this->assertSession()->elementExists('css', $selector);

      $this->assertNotContains('This block is broken or missing.', $moderation_element->getText(), 'The moderation element is not broken');

      foreach ($contains as $text) {
        $this->assertContains($text, $moderation_element->getText());
      }

      foreach ($not_contains as $text) {
        $this->assertNotContains($text, $moderation_element->getText());
      }
    }
  }

  /**
   * Tests the empty moderation activity chart.
   */
  public function testModerationActivityChartWithoutData() {
    $this->drupalGet('/user/' . $this->user->id() . '/moderation/dashboard');
    $drupal_js_settings = $this->getDrupalSettings();

    $this->assertTrue(!isset($drupal_js_settings['moderation_dashboard_activity']));

    $moderation_activity_element = $this->assertSession()->elementExists('css', '.block-moderation-dashboard-activity');
    $this->assertContains('There has been no editor activity within the last month.', $moderation_activity_element->getText());
  }

  /**
   * Tests moderation activity chart with some data.
   */
  public function testModerationActivityChartWithData() {
    $this->drupalCreateNode([
      'title' => 'Draft node',
      'moderation_state' => 'draft',
    ]);
    $this->drupalCreateNode([
      'title' => 'Published node',
      'moderation_state' => 'published',
    ]);

    $this->drupalGet('/user/' . $this->user->id() . '/moderation/dashboard');
    $drupal_js_settings = $this->getDrupalSettings();

    $this->assertTrue(count($drupal_js_settings['moderation_dashboard_activity']['datasets']) === 2);
  }

}
