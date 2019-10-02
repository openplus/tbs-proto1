<?php

namespace Drupal\entity_clone\EntityClone\Config;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_clone\EntityClone\EntityCloneInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigEntityCloneBase.
 */
class ConfigEntityCloneBase implements EntityHandlerInterface, EntityCloneInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * Constructs a new ConfigEntityCloneBase.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param string $entity_type_id
   *   The entity type ID.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, $entity_type_id) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeId = $entity_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $container->get('entity_type.manager'),
      $entity_type->id()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function cloneEntity(EntityInterface $entity, EntityInterface $cloned_entity, array $properties = []) {
    /** @var \Drupal\core\Config\Entity\ConfigEntityInterface $cloned_entity */
    $id_key = $this->entityTypeManager->getDefinition($this->entityTypeId)->getKey('id');
    $label_key = $this->entityTypeManager->getDefinition($this->entityTypeId)->getKey('label');

    // Set new entity properties.
    if (isset($properties['id'])) {
      if ($id_key) {
        $cloned_entity->set($id_key, $properties['id']);
      }
      unset($properties['id']);
    }

    if (isset($properties['label'])) {
      if ($label_key) {
        $cloned_entity->set($label_key, $properties['label']);
      }
      unset($properties['label']);
    }

    foreach ($properties as $key => $property) {
      $cloned_entity->set($key, $property);
    }

    $cloned_entity->save();
    return $cloned_entity;

  }

}
