<?php

namespace Drupal\content_translation;

use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;

@trigger_error('\Drupal\content_translation\ContentTranslationUpdatesManager is scheduled for removal in Drupal 9.0.0. Definitions are updated automatically now so no replacement is needed. See https://www.drupal.org/node/2973222.', E_USER_DEPRECATED);

/**
 * Provides the logic needed to update field storage definitions when needed.
 *
 * @deprecated in Drupal 8.6.x, to be removed before Drupal 9.0.0.
 *   Definitions are updated automatically now so no replacement is needed.
 *
 * @see https://www.drupal.org/node/2973222
 */
class ContentTranslationUpdatesManager {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $updateManager;

  /**
   * Constructs an updates manager instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $update_manager
   *   The entity definition update manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityDefinitionUpdateManagerInterface $update_manager) {
    $this->entityManager = $entity_manager;
    $this->updateManager = $update_manager;
  }

  /**
   * Executes field storage definition updates if needed.
   *
   * @param array $entity_types
   *   A list of entity type definitions to be processed.
   */
  public function updateDefinitions(array $entity_types) {
    // Handle field storage definition creation, if needed.
    if ($this->updateManager->needsUpdates()) {
      foreach ($entity_types as $entity_type_id => $entity_type) {
        $storage_definitions = $this->entityManager->getFieldStorageDefinitions($entity_type_id);
        $installed_storage_definitions = $this->entityManager->getLastInstalledFieldStorageDefinitions($entity_type_id);
        foreach (array_diff_key($storage_definitions, $installed_storage_definitions) as $storage_definition) {
          /** @var $storage_definition \Drupal\Core\Field\FieldStorageDefinitionInterface */
          if ($storage_definition->getProvider() == 'content_translation') {
            $this->updateManager->installFieldStorageDefinition($storage_definition->getName(), $entity_type_id, 'content_translation', $storage_definition);
          }
        }
      }
    }
  }

}
