<?php

namespace Drupal\Tests\library_manager\Functional;

/**
 * Tests library auto-load.
 *
 * @group library_manager
 */
class LibraryAutoLoadTest extends TestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node'];

  /**
   * Test callback.
   */
  public function testAutoLoad() {
    $this->createContentType(['type' => 'page']);
    // Create two nodes for testing.
    $this->createNode();
    $this->createNode();

    $assert_session = $this->assertSession();

    $edit = [
      'load' => TRUE,
      'visibility[request_path][pages]' => '/node/1',
    ];

    $this->drupalPostForm('admin/structure/library/definition/alpha/edit', $edit, 'Save');

    $this->drupalGet('node/1');
    $assert_session->responseContains('/sites/default/files/libraries/custom/alpha/example.css');
    $assert_session->responseContains('/sites/default/files/libraries/custom/alpha/example.js');

    $this->drupalGet('node/2');
    $assert_session->responseNotContains('/sites/default/files/libraries/custom/alpha/example.css');
    $assert_session->responseNotContains('/sites/default/files/libraries/custom/alpha/example.js');

    // Negate the condition and check the pages again.
    $edit['visibility[request_path][negate]'] = TRUE;
    $this->drupalPostForm('admin/structure/library/definition/alpha/edit', $edit, 'Save');
    $this->drupalGet('admin/structure/library/definition/alpha/edit');

    drupal_flush_all_caches();

    $this->drupalGet('node/1');
    $assert_session->responseNotContains('/sites/default/files/libraries/custom/alpha/example.css');
    $assert_session->responseNotContains('/sites/default/files/libraries/custom/alpha/example.js');

    $this->drupalGet('node/2');
    $assert_session->responseContains('/sites/default/files/libraries/custom/alpha/example.css');
    $assert_session->responseContains('/sites/default/files/libraries/custom/alpha/example.js');
  }

}
