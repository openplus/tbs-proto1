<?php

namespace Drupal\entity_clone;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Manage entity clone configuration.
 */
class EntityCloneSettingsManager {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The immutable entity clone settings configuration entity.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The editable entity clone settings configuration entity.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $editableConfig;

  /**
   * EntityCloneSettingsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->config = $config_factory->get('entity_clone.settings');
    $this->editableConfig = $config_factory->getEditable('entity_clone.settings');
  }

  /**
   * Get all content entity types.
   *
   * @return \Drupal\Core\Entity\ContentEntityTypeInterface[]
   *   An array containing all content entity types.
   */
  public function getContentEntityTypes() {
    $definitions = $this->entityTypeManager->getDefinitions();
    $ret = [];
    foreach ($definitions as $machine => $type) {
      if ($type instanceof ContentEntityTypeInterface) {
        $ret[$machine] = $type;
      }
    }

    return $ret;
  }

  /**
   * Set the entity clone settings.
   *
   * @param array $settings
   *   The settings from the form.
   */
  public function setFormSettings(array $settings) {
    if (isset($settings['table'])) {
      array_walk_recursive($settings['table'], function (&$item) {
        if ($item == '1') {
          $item = TRUE;
        }
        else {
          $item = FALSE;
        }
      });
      $this->editableConfig->set('form_settings', $settings['table'])->save();
    }
  }

  /**
   * Get the checkbox default value for a given entity type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return bool
   *   The default value.
   */
  public function getDefaultValue($entity_type_id) {
    $form_settings = $this->config->get('form_settings');
    if (isset($form_settings[$entity_type_id]['default_value'])) {
      return $form_settings[$entity_type_id]['default_value'];
    }
    return FALSE;
  }

  /**
   * Get the checkbox disable value for a given entity type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return bool
   *   The disable value.
   */
  public function getDisableValue($entity_type_id) {
    $form_settings = $this->config->get('form_settings');
    if (isset($form_settings[$entity_type_id]['disable'])) {
      return $form_settings[$entity_type_id]['disable'];
    }
    return FALSE;
  }

  /**
   * Get the checkbox hidden value for a given entity type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return bool
   *   The hidden value.
   */
  public function getHiddenValue($entity_type_id) {
    $form_settings = $this->config->get('form_settings');
    if (isset($form_settings[$entity_type_id]['hidden'])) {
      return $form_settings[$entity_type_id]['hidden'];
    }
    return FALSE;
  }

}
