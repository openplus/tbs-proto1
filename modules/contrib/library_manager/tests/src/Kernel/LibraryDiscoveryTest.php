<?php

namespace Drupal\Tests\library_manager\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Library manager plugins.
 *
 * @group library_manager
 */
class LibraryDiscoveryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['library_manager'];

  /**
   * Tests the library discovery service.
   */
  public function testLibraryDiscovery() {

    $this->installConfig(['library_manager']);

    $library_discovery = \Drupal::service('library_manager.library_discovery');

    $libraries = $library_discovery->getLibraries();
    $this->assertTrue(isset($libraries['library_manager/library_manager']));
    $this->assertFalse(isset($libraries['library_manager_test/library_manager_test']));

    $this->enableModules(['library_manager_test']);

    // Assert that library_manager_test library was found.
    $libraries = $library_discovery->getLibraries();
    $this->assertTrue(isset($libraries['library_manager/library_manager']));
    $this->assertTrue(isset($libraries['library_manager_test/library_manager_test']));

  }

}
