<?php

namespace Drupal\Tests\library_manager\Functional;

/**
 * Tests settings form.
 *
 * @group library_manager
 */
class SettingsFormTest extends TestBase {

  /**
   * Test callback.
   */
  public function testSettingsForm() {
    $this->drupalGet('admin/structure/library/settings');
    $form_prefix = '//form[@id = "library-manager-settings"]';
    $this->assertXpath($form_prefix . '//input[@name = "libraries_path" and @value = "sites/default/files/libraries/custom"]');
    $edit = [
      'libraries_path' => 'sites/default/files/foo',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertStatusMessage('The configuration options have been saved.');
    $this->assertXpath($form_prefix . '//input[@name = "libraries_path" and @value = "sites/default/files/foo"]');
  }

}
