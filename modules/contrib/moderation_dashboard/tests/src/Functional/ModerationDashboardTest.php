<?php

namespace Drupal\Tests\moderation_dashboard\Functional;

/**
 * Contains tests for the Moderation Dashboard module.
 *
 * @group moderation_dashboard
 */
class ModerationDashboardTest extends ModerationDashboardTestBase {

  /**
   * {@inheritdoc}
   */
  public $setEditorialWorkflow = FALSE;

  /**
   * {@inheritdoc}
   */
  public $testNodeTypes = [];

  /**
   * Tests that the Moderation Dashboard loads as expected.
   */
  public function testModerationDashboardLoads() {
    // Deny access for Anonymous users.
    $this->drupalGet('/user/' . $this->user->id() . '/moderation/dashboard');
    $this->assertSession()->statusCodeEquals(403);

    // Deny access if no Content Type has moderation enabled.
    $this->drupalLogin($this->user);
    $this->drupalGet('/user/' . $this->user->id() . '/moderation/dashboard');
    $this->assertSession()->statusCodeEquals(403);

    // Deny access if no moderated Node has been created (fresh install).
    $this->drupalCreateContentType([
      'type' => 'page',
    ]);
    $this->drupalGet('/user/' . $this->user->id() . '/moderation/dashboard');
    $this->assertSession()->statusCodeEquals(403);

    // Allow access if everything looks good.
    $this->editorialWorkflow->getTypePlugin()->addEntityTypeAndBundle('node', 'page');
    $this->editorialWorkflow->save();

    $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Test title first revision',
      'moderation_state' => 'published',
    ]);
    $this->drupalGet('/user/' . $this->user->id() . '/moderation/dashboard');
    $this->assertSession()->statusCodeEquals(200);
  }

}
