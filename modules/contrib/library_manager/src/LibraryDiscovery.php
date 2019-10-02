<?php

namespace Drupal\library_manager;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Asset\LibraryDiscovery as BaseLibraryDiscovery;
use Drupal\Core\Cache\CacheCollectorInterface;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Serialization\Yaml;

/**
 * Discovers available asset libraries.
 */
class LibraryDiscovery extends BaseLibraryDiscovery implements LibraryDiscoveryInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * Constructs the controller object.
   *
   * @param \Drupal\Core\Cache\CacheCollectorInterface $library_discovery_collector
   *   The library discovery cache collector.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   * @param string $root
   *   The app root.
   */
  public function __construct(CacheCollectorInterface $library_discovery_collector, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, $root) {
    parent::__construct($library_discovery_collector);
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->root = $root;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    $libraries = [];
    foreach ($this->getEnabledExtensions() as $extension) {
      foreach ($this->getLibrariesByExtension($extension) as $library_name => $library_info) {
        $library_info['extension'] = $extension;
        $library_info['name'] = $library_name;
        $libraries[$library_info['extension'] . '/' . $library_info['name']] = $library_info;
      }
    }

    return $libraries;
  }

  /**
   * Returns enabled extensions.
   *
   * @return array
   *   List of enabled extension including core.
   */
  protected function getEnabledExtensions() {
    $enabled_extensions = ['core'];

    $listing = new ExtensionDiscovery(DRUPAL_ROOT);
    $listing->setProfileDirectories([]);

    $extensions = $listing->scan('module', TRUE);
    $extensions += $listing->scan('profile', TRUE);
    $extensions += $listing->scan('theme', TRUE);

    foreach ($extensions as $extension) {
      $extension_name = $extension->getName();
      // Do not check libraries for disabled extensions.
      if ($extension->getType() == 'theme') {
        if (!$this->themeHandler->themeExists($extension_name)) {
          continue;
        }
      }
      elseif (!$this->moduleHandler->moduleExists($extension_name)) {
        continue;
      }
      $enabled_extensions[] = $extension_name;
    }

    return $enabled_extensions;
  }

  /**
   * {@inheritdoc}
   */
  public function exportLibraryByName($extension, $library) {
    $libraries = [];

    $library_file = $this->getExtensionPath($extension) . $extension . '.libraries.yml';
    if (file_exists($this->root . '/' . $library_file)) {
      $libraries = Yaml::decode(file_get_contents($this->root . '/' . $library_file));
    }

    // Allow modules to add dynamic library definitions.
    if ($this->moduleHandler->implementsHook($extension, 'library_info_build')) {
      $libraries = NestedArray::mergeDeep($libraries, $this->moduleHandler->invoke($extension, 'library_info_build'));
    }

    // Allow modules to alter the module's registered libraries.
    $this->moduleHandler->alter('library_info', $libraries, $extension);
    return isset($libraries[$library]) ? $libraries[$library] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionPath($extension) {
    $path = $extension == 'core' ?
      'core' :
      drupal_get_path($this->moduleHandler->moduleExists($extension) ? 'module' : 'theme', $extension);

    return $path . '/';
  }

  /**
   * {@inheritdoc}
   */
  public function processLibraryVersion($version) {
    if ($version === 'VERSION') {
      $version = \Drupal::VERSION;
    }
    // Remove 'v' prefix from external library versions.
    elseif ($version[0] === 'v') {
      $version = substr($version, 1);
    }
    return $version;
  }

}
