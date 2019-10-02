<?php

namespace Drupal\Tests\library_manager\Functional;

/**
 * Tests the Library definition interface.
 *
 * @group library_manager
 */
class LibraryDefinitionBuildFormTest extends TestBase {

  /**
   * Test callback.
   */
  public function testCreateDefinition() {

    $library_discovery = \Drupal::service('library_manager.library_discovery');

    // Set Stark as active theme to avoid Stable theme CSS overrides.
    $stark = \Drupal::service('theme.initialization')->initTheme('stark');
    \Drupal::theme()->setActiveTheme($stark);

    $libraries = [
      'core/backbone',
      'library_manager/alpha',
      'core/drupal',
      'system/base',
      'classy/user',
    ];

    foreach ($libraries as $library_id) {
      list($extension, $library) = explode('/', $library_id);

      $this->drupalGet('admin/structure/library');

      $library_url = sprintf('admin/structure/library/library/%s/%s', $extension, $library);
      $this->clickLinkInRow($extension . '/' . $library, 'Create definition');
      $this->assertSession()->linkByHrefExists($library_url);

      $this->assertPageTitle('Create library definition');
      $cloned_library_id = $extension . '_' . $library . '_cloned';

      $this->drupalPostForm(NULL, ['id' => $cloned_library_id], 'Create');
      $this->assertStatusMessage('Library definition has been created.');
      $this->assertPageTitle('Edit library definition');

      // Register a new library to compare its properties with original.
      $this->drupalPostForm(NULL, ['mode' => 'new'], 'Save');

      drupal_static_reset('library_manager_build_libraries');
      $library_info = $library_discovery->exportLibraryByName($extension, $library);
      $cloned_library_info = $library_discovery->exportLibraryByName('library_manager', $cloned_library_id);

      if (isset($library_info['remote'])) {
        $this->assertEquals($library_info['remote'], $cloned_library_info['remote']);
      }

      if (isset($library_info['license'])) {
        $this->assertEquals($library_info['license'], $cloned_library_info['license']);
      }

      $library_info['version'] = $library_info['version'] == 'VERSION' ? \Drupal::VERSION : $library_info['version'];
      $this->assertEquals($library_info['version'], $cloned_library_info['version'], 'Version is the same.');
      if (isset($library_info['dependencies'])) {
        $this->assertEquals($library_info['dependencies'], $cloned_library_info['dependencies']);
      }

      $library_info['js'] = isset($library_info['js']) ? $library_info['js'] : [];
      $this->assertEquals(count($library_info['js']), count($cloned_library_info['js']));
      foreach ($library_info['js'] as $file_name => $file) {
        $cloned_file_name = key($cloned_library_info['js']);
        $cloned_file = array_shift($cloned_library_info['js']);
        $this->assertEquals(basename($file_name), basename($cloned_file_name));
        $this->assertEqualProperties($file, $cloned_file, 'minified');
        $this->assertEqualProperties($file, $cloned_file, 'preprocess');
        $this->assertEqualProperties($file, $cloned_file, 'weight');
        $this->assertEqualProperties($file, $cloned_file, 'type');
      }

      $library_info['css'] = isset($library_info['css']) ? $library_info['css'] : [];
      $this->assertEquals(count($library_info['css']), count($cloned_library_info['css']));
      foreach ($library_info['css'] as $group => $files) {
        foreach ($files as $file_name => $file) {
          $cloned_file_name = key($cloned_library_info['css'][$group]);
          $cloned_file = array_shift($cloned_library_info['css'][$group]);
          $this->assertEquals(basename($file_name), basename($cloned_file_name));
          $this->assertEqualProperties($file, $cloned_file, 'minified');
          $this->assertEqualProperties($file, $cloned_file, 'preprocess');
          $this->assertEqualProperties($file, $cloned_file, 'weight');
          $this->assertEqualProperties($file, $cloned_file, 'type');
        }
      }

      // Get processed library info to compare file contents.
      $library_info = $library_discovery->getLibraryByName($extension, $library);
      $cloned_library_info = $library_discovery->getLibraryByName('library_manager', $cloned_library_id);
      $library_discovery->clearCachedDefinitions();
      foreach (['js', 'css'] as $file_type) {
        foreach ($library_info[$file_type] as $delta => $file) {
          if ($file['type'] == 'file') {
            $content_hash = md5_file(DRUPAL_ROOT . '/' . $file['data']);
            $cloned_content_hash = md5_file(DRUPAL_ROOT . '/' . $cloned_library_info[$file_type][$delta]['data']);
            $this->assertEquals($content_hash, $cloned_content_hash, 'Files are identical');
          }
        }
      }
    }

  }

  /**
   * Passes if two file properties are equal.
   */
  protected function assertEqualProperties($file_1, $file_2, $property_name, $default = FALSE) {
    $property_1 = isset($file_1[$property_name]) ? $file_1[$property_name] : $default;
    $property_2 = isset($file_2[$property_name]) ? $file_2[$property_name] : $default;
    $this->assertEquals(
      $property_1,
      $property_2,
      sprintf('%s property is the same.', ucfirst($property_name))
    );
  }

}
