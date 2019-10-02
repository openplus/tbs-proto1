<?php

namespace Drupal\wxt_library\TwigExtension;

class LibraryTwig extends \Twig_Extension {

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('wxtlibrary', [$this, 'getLibraryPath']),
    ];
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'wxt_library.twig_extension';
  }

  /**
   * Generates the full path of the specified theme.
   */
  public static function getLibraryPath($theme) {
    return _wxt_library_get_path($theme, TRUE);
  }

}
