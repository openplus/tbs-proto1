<?php

namespace Drupal\Tests\moderation_dashboard\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\content_moderation\Traits\ContentModerationTestTrait;

/**
 * Tests moderation dashboard permissions.
 *
 * @group moderation_dashboard
 */
class ModerationDashboardPermissionTest extends BrowserTestBase {

  use ContentModerationTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'moderation_dashboard',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create a moderated entity type.
    $this->drupalCreateContentType([
      'type' => 'page',
    ]);
    $workflow = $this->createEditorialWorkflow();
    $workflow->getTypePlugin()->addEntityTypeAndBundle('node', 'page');
    $workflow->save();
  }

  /**
   * The test data.
   *
   * @var array
   */
  protected $canViewOwnDashboardCases = [
    [
      'permissions' => ['use moderation dashboard'],
    ],
    [
      'permissions' => ['view any moderation dashboard', 'use moderation dashboard'],
    ],
  ];

  /**
   * Tests if a user can view their dashboard with permission.
   */
  public function testCanViewOwnDashboard() {
    foreach ($this->canViewOwnDashboardCases as $i => $testCase) {
      $user = $this->createUser($testCase['permissions']);
      $this->drupalLogin($user);
      $this->assertSession()->addressEquals("/user/{$user->id()}/moderation/dashboard");
      $status_code = $this->getSession()->getStatusCode();
      $message = "#$i: expected 200, got $status_code.";
      $this->assertEquals(200, $status_code, $message);
    }
  }

  /**
   * The test data.
   *
   * @var array
   */
  protected $canNotViewOwnDashboardCases = [
    [
      'permissions' => [],
    ],
    [
      'permissions' => ['view any moderation dashboard'],
    ],
  ];

  /**
   * Tests that a user can't view their dashboard without permission.
   */
  public function testCanNotViewOwnDashboard() {
    foreach ($this->canNotViewOwnDashboardCases as $i => $testCase) {
      $user = $this->createUser($testCase['permissions']);
      $this->drupalLogin($user);
      $this->drupalGet("/user/{$user->id()}/moderation/dashboard");
      $status_code = $this->getSession()->getStatusCode();
      $message = "#$i: expected 403, got $status_code.";
      $this->assertEquals(403, $status_code, $message);
    }
  }

  /**
   * The test data.
   *
   * @var array
   */
  protected $canViewOtherDashboardCases = [
    [
      'permissions_a' => ['view any moderation dashboard'],
      'permissions_b' => ['use moderation dashboard'],
    ],
    [
      'permissions_a' => ['view any moderation dashboard'],
      'permissions_b' => ['view any moderation dashboard', 'use moderation dashboard'],
    ],
    [
      'permissions_a' => ['view any moderation dashboard', 'use moderation dashboard'],
      'permissions_b' => ['use moderation dashboard'],
    ],
    [
      'permissions_a' => ['view any moderation dashboard', 'use moderation dashboard'],
      'permissions_b' => ['view any moderation dashboard', 'use moderation dashboard'],
    ],
  ];

  /**
   * Tests if a user can view other dashboards with permission.
   */
  public function testCanViewOtherDashboard() {
    foreach ($this->canViewOtherDashboardCases as $i => $testCase) {
      $user_a = $this->createUser($testCase['permissions_a']);
      $user_b = $this->createUser($testCase['permissions_b']);
      $this->drupalLogin($user_a);
      $this->drupalGet("/user/{$user_b->id()}/moderation/dashboard");
      $status_code = $this->getSession()->getStatusCode();
      $message = "#$i: expected 200, got $status_code.";
      $this->assertEquals(200, $status_code, $message);
    }
  }

  /**
   * The test data.
   *
   * @var array
   */
  protected $canNotViewOtherDashboardCases = [
    // User B doesn't have a dashboard, therefore nobody can view it.
    [
      'permissions_a' => [],
      'permissions_b' => [],
    ],
    [
      'permissions_a' => [],
      'permissions_b' => ['view any moderation dashboard'],
    ],
    [
      'permissions_a' => ['view any moderation dashboard'],
      'permissions_b' => [],
    ],
    [
      'permissions_a' => ['view any moderation dashboard'],
      'permissions_b' => ['view any moderation dashboard'],
    ],
    [
      'permissions_a' => ['view any moderation dashboard', 'use moderation dashboard'],
      'permissions_b' => [],
    ],
    [
      'permissions_a' => ['view any moderation dashboard', 'use moderation dashboard'],
      'permissions_b' => ['view any moderation dashboard'],
    ],
    [
      'permissions_a' => ['use moderation dashboard'],
      'permissions_b' => [],
    ],
    [
      'permissions_a' => ['use moderation dashboard'],
      'permissions_b' => ['view any moderation dashboard'],
    ],
    // User A doesn't have permission to view User B's dashboard.
    [
      'permissions_a' => [],
      'permissions_b' => ['use moderation dashboard'],
    ],
    [
      'permissions_a' => [],
      'permissions_b' => ['view any moderation dashboard', 'use moderation dashboard'],
    ],
    [
      'permissions_a' => ['use moderation dashboard'],
      'permissions_b' => ['use moderation dashboard'],
    ],
    [
      'permissions_a' => ['use moderation dashboard'],
      'permissions_b' => ['view any moderation dashboard', 'use moderation dashboard'],
    ],
  ];

  /**
   * Tests that a user can't view other dashboards without permission.
   */
  public function testCanNotViewOtherDashboard() {
    foreach ($this->canNotViewOtherDashboardCases as $i => $testCase) {
      $user_a = $this->createUser($testCase['permissions_a']);
      $user_b = $this->createUser($testCase['permissions_b']);
      $this->drupalLogin($user_a);
      $this->drupalGet("/user/{$user_b->id()}/moderation/dashboard");
      $status_code = $this->getSession()->getStatusCode();
      $message = "#$i: expected 403, got $status_code.";
      $this->assertEquals(403, $status_code, $message);
    }
  }

}
