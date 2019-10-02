<?php

namespace Drupal\Tests\moderation_dashboard\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\content_moderation\Traits\ContentModerationTestTrait;

/**
 * Defines a base class for testing Moderation Dashboard module.
 */
abstract class ModerationDashboardTestBase extends BrowserTestBase {

  use ContentModerationTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['moderation_dashboard'];

  /**
   * Permissions of the test user.
   *
   * @var string[]
   */
  public $userPermissions = [
    'access content',
    'use moderation dashboard',
    'view all revisions',
    'view any moderation dashboard',
  ];

  /**
   * Set to FALSE to skip adding editorial workflow to test node types.
   *
   * @var bool
   */
  public $setEditorialWorkflow = TRUE;

  /**
   * The test node types.
   *
   * @var array
   *   An array of node type properties.
   */
  public $testNodeTypes = [['type' => 'page']];

  /**
   * Test user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The editorial workflow.
   *
   * @var \Drupal\workflows\WorkflowInterface||null
   */
  protected $editorialWorkflow;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create content types for tests.
    foreach ($this->testNodeTypes as $node_type_properties) {
      $this->drupalCreateContentType($node_type_properties);
    }

    // Create editorial workflow.
    $this->editorialWorkflow = $this->createEditorialWorkflow();

    if ($this->setEditorialWorkflow) {
      foreach ($this->testNodeTypes as $node_type_properties) {
        $this->editorialWorkflow->getTypePlugin()->addEntityTypeAndBundle('node', $node_type_properties['type']);
      }
      $this->editorialWorkflow->save();
    }

    // Create test user.
    $this->user = $this->createUser($this->userPermissions, 'test user');
  }

}
