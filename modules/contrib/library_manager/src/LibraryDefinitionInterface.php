<?php

namespace Drupal\library_manager;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a library definition entity type.
 */
interface LibraryDefinitionInterface extends ConfigEntityInterface {

  /**
   * Returns information about a single JS file.
   */
  public function getJsFile($file_name);

  /**
   * Returns information about a single CSS file.
   */
  public function getCssFile($file_name);

  /**
   * Returns an array of visibility condition configurations.
   *
   * @return array
   *   An array of visibility condition configuration keyed by the condition ID.
   */
  public function getVisibility();

  /**
   * Gets conditions for this block.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array or collection of configured condition plugins.
   */
  public function getVisibilityConditions();

  /**
   * Gets a visibility condition plugin instance.
   *
   * @param string $instance_id
   *   The condition plugin instance ID.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   A condition plugin.
   */
  public function getVisibilityCondition($instance_id);

  /**
   * Sets the visibility condition configuration.
   *
   * @param string $instance_id
   *   The condition instance ID.
   * @param array $configuration
   *   The condition configuration.
   *
   * @return $this
   */
  public function setVisibilityConfig($instance_id, array $configuration);

}
