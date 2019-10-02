<?php

namespace Drupal\Tests\library_manager\Unit;

use Drupal\Tests\Core\Menu\LocalTaskIntegrationTestBase;

/**
 * Tests Library manager local tasks.
 *
 * @group library_manager
 */
class LocalTasksTest extends LocalTaskIntegrationTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // The module can be located in subdirectory.
    preg_match('#.*/(modules/.*library_manager)/tests/src/Unit$#', __DIR__, $matches);
    $this->directoryList = ['library_manager' => $matches[1]];
    parent::setUp();
  }

  /**
   * Tests local task existence.
   */
  public function testActionLocalTasks() {
    $this->assertLocalTasks(
      'library_manager.library_collection',
      [
        [
          'library_manager.libraries',
          'library_manager.entity.library_definition.collection',
          'library_manager.settings',
        ],
      ]
    );
    $this->assertLocalTasks(
      'library_manager.library_canonical',
      [
        [
          'library_manager.library_canonical',
          'library_manager.library_export',
        ],
      ]
    );
  }

}
