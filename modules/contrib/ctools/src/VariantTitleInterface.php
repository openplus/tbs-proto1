<?php

namespace Drupal\ctools;

/**
 * Provides a title for BlockDisplayVariant plugins.
 */
interface VariantTitleInterface {

  /**
   * Retrieves the display title of this variant.
   *
   * @return string
   *   The title of this variant.
   */
  public function getTitle();

}
