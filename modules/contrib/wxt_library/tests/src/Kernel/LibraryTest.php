<?php

namespace Drupal\Tests\wxt_library\Kernel;

use Drupal\config\Tests\SchemaCheckTestTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test migration config entity discovery.
 *
 * @group wxt
 * @group wxt_library
 */
class LibraryTest extends KernelTestBase {

  use SchemaCheckTestTrait;

  /**
   * Installation profile.
   *
   * @var string
   */
  protected $profile = 'wxt';

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'tour',
    'wxt_library',
  ];

  /**
   * Stores the wxt_library plugin manager.
   *
   * @var \Drupal\wxt_library\LibraryService
   */
  protected $pluginManager;

  protected function setUp() {
    parent::setUp();

    $this->installConfig('wxt_library');
    $this->pluginManager = $this->container->get('wxt_library.service_wxt');
  }

  public function testLibrary() {
    $this->assertSame('wet_boew', $this->pluginManager->getLibraryName());
    $this->assertSame('/libraries/theme-wet-boew', $this->pluginManager->getLibraryPath());
  }

}
