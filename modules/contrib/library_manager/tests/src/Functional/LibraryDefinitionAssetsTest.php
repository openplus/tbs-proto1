<?php

namespace Drupal\Tests\library_manager\Functional;

/**
 * Tests library definition assets.
 *
 * @group library_manager
 */
class LibraryDefinitionAssetsTest extends TestBase {

  /**
   * Test callback.
   */
  public function testCreateDefinition() {

    $libraries_path = DRUPAL_ROOT . '/sites/default/files/libraries/custom/';

    $edit = [
      'id' => 'beta',
      'version' => '1.0.0',
    ];
    $this->drupalPostForm('admin/structure/library/definition/add', $edit, 'Save');

    // Create new file.
    $edit = [
      'file_name' => 'example-1.js',
      'code' => 'alert(123)',
    ];
    $this->drupalPostForm('admin/structure/library/definition/beta/js/add', $edit, 'Save');
    $this->assertTrue(file_exists($libraries_path . 'beta/example-1.js'));

    // Rename the file.
    $edit = [
      'file_name' => 'example-2.js',
    ];
    $this->drupalPostForm('admin/structure/library/definition/beta/js/1/edit', $edit, 'Save');
    $this->assertFalse(file_exists($libraries_path . 'beta/example-1.js'));
    $this->assertTrue(file_exists($libraries_path . 'beta/example-2.js'));

    // Delete the file.
    $this->drupalPostForm('admin/structure/library/definition/beta/js/1/delete', [], 'Delete');
    $this->assertFalse(file_exists($libraries_path . 'beta/example-2.js'));

    // Create new file.
    $edit = [
      'file_name' => 'example-3.css',
      'code' => 'body {color: blue;}',
    ];
    $this->drupalPostForm('admin/structure/library/definition/beta/css/add', $edit, 'Save');
    $this->assertTrue(file_exists($libraries_path . 'beta/example-3.css'));

    // Delete the definition.
    $this->drupalPostForm('admin/structure/library/definition/beta/delete', [], 'Delete');
    $this->assertFalse(file_exists($libraries_path . 'beta'));
  }

}
