<?php

namespace Drupal\entity_clone\EntityClone\Content;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\entity_clone\EntityClone\EntityCloneInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentEntityCloneBase.
 */
class ContentEntityCloneBase implements EntityHandlerInterface, EntityCloneInterface {

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
   * Constructs a new ContentEntityCloneBase.
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
  public function cloneEntity(EntityInterface $entity, EntityInterface $cloned_entity, array $properties = [], array &$already_cloned = []) {
    // Clone referenced entities.
    $cloned_entity->save();
    $already_cloned[$entity->getEntityTypeId()][$entity->id()] = $cloned_entity;
    if ($cloned_entity instanceof FieldableEntityInterface && $entity instanceof FieldableEntityInterface) {
      foreach ($cloned_entity->getFieldDefinitions() as $field_id => $field_definition) {
        if ($this->fieldIsClonable($field_definition)) {
          $field = $entity->get($field_id);
          /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $value */
          if ($field->count() > 0) {
            $cloned_entity->set($field_id, $this->cloneReferencedEntities($field, $field_definition, $properties, $already_cloned));
          }
        }
      }
    }

    $this->setClonedEntityLabel($entity, $cloned_entity);
    $cloned_entity->save();
    return $cloned_entity;
  }

  /**
   * Determines if a field is clonable.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return bool
   *   TRUE if th field is clonable; FALSE otherwise.
   */
  protected function fieldIsClonable(FieldDefinitionInterface $field_definition) {
    $clonable_field_types = [
      'entity_reference',
      'entity_reference_revisions',
    ];

    $type_is_clonable = in_array($field_definition->getType(), $clonable_field_types, TRUE);
    if (($field_definition instanceof FieldConfigInterface) && $type_is_clonable) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Sets the cloned entity's label.
   *
   * @param \Drupal\Core\Entity\EntityInterface $original_entity
   *   The original entity.
   * @param \Drupal\Core\Entity\EntityInterface $cloned_entity
   *   The entity cloned from the original.
   */
  protected function setClonedEntityLabel(EntityInterface $original_entity, EntityInterface $cloned_entity) {
    $label_key = $this->entityTypeManager->getDefinition($this->entityTypeId)->getKey('label');
    if ($label_key && $cloned_entity->hasField($label_key)) {
      $cloned_entity->set($label_key, $original_entity->label() . ' - Cloned');
    }
  }

  /**
   * Clone referenced entities.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field item.
   * @param \Drupal\Core\Field\FieldConfigInterface $field_definition
   *   The field definition.
   * @param array $properties
   *   All new properties to replace old.
   * @param array $already_cloned
   *   List of all already cloned entities, used for circular references.
   *
   * @return array
   *   Referenced entities.
   */
  protected function cloneReferencedEntities(FieldItemListInterface $field, FieldConfigInterface $field_definition, array $properties, array &$already_cloned) {
    $referenced_entities = [];
    foreach ($field as $value) {
      // Check if we're not dealing with an entity
      // that has been deleted in the meantime.
      if (!$referenced_entity = $value->get('entity')->getTarget()) {
        continue;
      }
      /** @var \Drupal\Core\Entity\ContentEntityInterface $referenced_entity */
      $referenced_entity = $value->get('entity')->getTarget()->getValue();
      $child_properties = $this->getChildProperties($properties, $field_definition, $referenced_entity);
      if (!empty($child_properties['clone'])) {

        $cloned_reference = $referenced_entity->createDuplicate();
        /** @var \Drupal\entity_clone\EntityClone\EntityCloneInterface $entity_clone_handler */
        $entity_clone_handler = $this->entityTypeManager->getHandler($referenced_entity->getEntityTypeId(), 'entity_clone');
        $entity_clone_handler->cloneEntity($referenced_entity, $cloned_reference, $child_properties['children'], $already_cloned);

        $referenced_entities[] = $cloned_reference;
      }
      elseif (!empty($child_properties['is_circular'])) {
        if (!empty($already_cloned[$referenced_entity->getEntityTypeId()][$referenced_entity->id()])) {
          $referenced_entities[] = $already_cloned[$referenced_entity->getEntityTypeId()][$referenced_entity->id()];
        }
      }
      else {
        $referenced_entities[] = $referenced_entity;
      }
    }
    return $referenced_entities;
  }

  /**
   * Fetches the properties of a child entity.
   *
   * @param array $properties
   *   Properties of the clone operation.
   * @param \Drupal\Core\Field\FieldConfigInterface $field_definition
   *   The field definition.
   * @param \Drupal\Core\Entity\EntityInterface $referenced_entity
   *   The field's target entity.
   *
   * @return array
   *   Child properties.
   */
  protected function getChildProperties(array $properties, FieldConfigInterface $field_definition, EntityInterface $referenced_entity) {
    $child_properties = [];
    if (isset($properties['recursive'][$field_definition->id()]['references'][$referenced_entity->id()])) {
      $child_properties = $properties['recursive'][$field_definition->id()]['references'][$referenced_entity->id()];
    }
    if (!isset($child_properties['children'])) {
      $child_properties['children'] = [];
    }
    return $child_properties;
  }

}
