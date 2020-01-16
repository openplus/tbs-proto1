<?php

namespace Drupal\openplus_rest\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Provides node destination, updating only markup fields.
 *
 * @MigrateDestination(
 *   id = "markup_cleanse",
 * )
 */
class MarkupCleanse extends EntityContentBase {

  /**
   * Entity type.
   *
   * @var string $entityType
   */
  public static $entityType = 'node';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return parent::create($container, $configuration, 'entity:' . static::$entityType, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    // By default, an update will be preserved.
    $rollback_action = MigrateIdMapInterface::ROLLBACK_PRESERVE;

    // Make sure we have the right translation.
    if ($this->isTranslationDestination()) {
      $property = $this->storage->getEntityType()->getKey('langcode');
      if ($row->hasDestinationProperty($property)) {
        $language = $row->getDestinationProperty($property);
        if (!$entity->hasTranslation($language)) {
          $entity->addTranslation($language);

          // We're adding a translation, so delete it on rollback.
          $rollback_action = MigrateIdMapInterface::ROLLBACK_DELETE;
        }
        $entity = $entity->getTranslation($language);
      }
    }

    // If the migration has specified a list of properties to be overwritten,
    // clone the row with an empty set of destination values, and re-add only
    // the specified properties.
    if (isset($this->configuration['overwrite_properties'])) {
      $clone = $row->cloneWithoutDestination();
      foreach ($this->configuration['overwrite_properties'] as $property) {
        $clone->setDestinationProperty($property, $row->getDestinationProperty($property));
      }
      $row = $clone;
    }

    foreach ($row->getDestination() as $field_name => $values) {
      $field = $entity->$field_name;
      if ($field instanceof TypedDataInterface) {
        $definition = $field->getDefinition();
        // update long text field only 
        //if (empty($field->getValue())) {
         // $field->setValue($values);
        //}
      }
    }

    $this->setRollbackAction($row->getIdMap(), $rollback_action);

    // We might have a different (translated) entity, so return it.
    return $entity;
  }

}
