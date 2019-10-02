<?php

namespace Drupal\library_manager;

use Drupal\Core\Asset\LibraryDiscoveryInterface as BaseLibraryDiscoveryInterface;

/**
 * Discovers available asset libraries in all installed Drupal extensions.
 */
interface LibraryDiscoveryInterface extends BaseLibraryDiscoveryInterface {

  /**
   * Gets all libraries defined by all extension.
   *
   * @return array
   *   An associative array of libraries.
   */
  public function getLibraries();

  /**
   * Returns the path to a system item (core, module, theme, etc.).
   */
  public function getExtensionPath($extension);

  /**
   * Normalizes library version.
   */
  public function processLibraryVersion($version);

  /**
   * Gets a single library defined by an extension by name.
   *
   * @param string $extension
   *   The name of the extension that registered a library.
   * @param string $library
   *   The name of a registered library to retrieve.
   *
   * @return array|false
   *   The definition of the requested library as it declared in the extension.
   */
  public function exportLibraryByName($extension, $library);

}
