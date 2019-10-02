<?php

namespace Drupal\lightning_scheduler;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\lightning_core\Element;

/**
 * This class is final because the migration is not an API and should not be
 * extended or re-used.
 */
final class Migrator {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Migrator constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   (optional) The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database, StateInterface $state, MessengerInterface $messenger, TranslationInterface $translation = NULL) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->state = $state;
    $this->messenger = $messenger;

    if ($translation) {
      $this->setStringTranslation($translation);
    }
  }

  /**
   * Returns the entity types still needing migration.
   *
   * @return string[]
   *   The entity type IDs that still need to be migrated.
   */
  public function getMigrations() {
    return $this->state->get('lightning_scheduler.migrations', []);
  }

  /**
   * Sets the entity types still needing migration.
   *
   * @param string[] $migrations
   *   The entity type IDs that still need to be migrated.
   */
  public function setMigrations(array $migrations) {
    return $this->state->set('lightning_scheduler.migrations', $migrations);
  }

  /**
   * Returns all content entity types which need to be migrated.
   *
   * @param string[] $limit
   *   (optional) An array of entity type IDs. If given, only those entity types
   *   will be considered.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   *   The entity types that need to be migrated.
   */
  public function getEntityTypesToMigrate(array $limit = []) {
    // Only content entities which have been marked as needing migration are
    // considered.
    $filter = function (EntityTypeInterface $entity_type) {
      return $entity_type->entityClassImplements(ContentEntityInterface::class);
    };
    $entity_types = array_filter($this->entityTypeManager->getDefinitions(), $filter);

    $migrations = $limit ?: $this->getMigrations();
    $migrations = array_flip($migrations);

    return array_intersect_key($entity_types, $migrations);
  }

  /**
   * Queries for all entities of a specific type which need to be migrated.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The prepared query.
   */
  public function query($entity_type_id) {
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);

    $fields = [
      $entity_type->getKey('id'),
      'scheduled_publication',
      'scheduled_moderation_state',
    ];
    if ($entity_type->isRevisionable()) {
      array_push($fields, $entity_type->getKey('revision'));
    }
    if ($entity_type->isTranslatable()) {
      array_push($fields, $entity_type->getKey('langcode'));
    }

    $table = $entity_type->getRevisionDataTable() ?: $entity_type->getDataTable();

    return $this->database
      ->select($table)
      ->fields($table, $fields)
      ->isNotNull('scheduled_publication')
      ->isNotNull('scheduled_moderation_state');
  }

  /**
   * Migrates all entities of a specific type.
   *
   * @param string $entity_type_id
   *   The entity type ID to migrate.
   * @param callable $callback
   *   (optional) A callback to invoke after each item is migrated. It receives
   *   the entity type ID, the number of items migrated so far, the item itself
   *   (which will be an \stdClass object), and the migrator service.
   *
   * @return int
   *   The number of items that were migrated.
   */
  public function migrateAll($entity_type_id, callable $callback = NULL) {
    $items = $this->query($entity_type_id)->execute();
    $count = 0;

    foreach ($items as $item) {
      $this->migrate($entity_type_id, $item);

      if ($callback) {
        $callback($entity_type_id, ++$count, $item, $this);
      }
    }
    $this->completeMigration($entity_type_id);

    return $count;
  }

  /**
   * Migrates a single entity.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param \stdClass $item
   *   The relevant entity information, as returned from query(). This will
   *   include the entity ID and values of the scheduled_moderation_state and
   *   scheduled_publication fields. Will also include the revision ID and
   *   langcode, if the entity type is revisionable and translatable,
   *   respectively.
   */
  public function migrate($entity_type_id, \stdClass $item) {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);

    $entity = $this->load($storage, $item);

    // A horrible hack to work around Content Moderation's opinions being a
    // little too strong. See lightning_scheduler_entity_presave().
    $entity->existingRevisionId = $entity->getRevisionId();

    $entity
      ->set('scheduled_transition_date', $item->scheduled_publication)
      ->set('scheduled_transition_state', $item->scheduled_moderation_state)
      // Deep in the SqlContentEntityStorage handler, I found a strange thing:
      // field values are not actually written to the database during save
      // unless they've changed. VURT DA FURK! This quirk can totally break the
      // migration under certain circumstances, but ensuring that the original
      // entity is set, with different values on the fields to be written,
      // prevents that.
      ->original
      ->set('scheduled_transition_date', NULL)
      ->set('scheduled_transition_state', NULL);

    $storage->save($entity);
  }

  /**
   * Purges the data for a single base field field on a single entity type.
   *
   * Only entity types with SQL storage are supported.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $field_name
   *   The name of the field to purge data.
   */
  public function purge($entity_type_id, $field_name) {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);

    if ($storage instanceof SqlEntityStorageInterface) {
      $table_mapping = $storage->getTableMapping();

      $values = [];
      foreach ($table_mapping->getColumnNames($field_name) as $column) {
        $values[$column] = NULL;
      }

      $this->database
        ->update($table_mapping->getFieldTableName($field_name))
        ->fields($values)
        ->execute();
    }
  }

  /**
   * Loads an entity to be migrated.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage handler.
   * @param \stdClass $item
   *   The relevant entity information. See ::migrate() for details.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The loaded entity, with $entity->original set.
   */
  protected function load(EntityStorageInterface $storage, \stdClass $item) {
    $entity_type = $storage->getEntityType();

    $id_key = $entity_type->getKey('id');

    if ($entity_type->isRevisionable()) {
      $vid_key = $entity_type->getKey('revision');
    }
    if ($entity_type->isTranslatable()) {
      $language_key = $entity_type->getKey('langcode');
    }

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = isset($vid_key)
      ? $storage->loadRevision($item->$vid_key)
      : $storage->load($item->$id_key);

    if (isset($language_key)) {
      $entity = $entity->getTranslation($item->$language_key);
    }

    $entity->original = $storage->loadUnchanged($item->$id_key);

    return $entity;
  }

  /**
   * Completes the migration for a single entity type.
   *
   * If the scheduled_publication or scheduled_moderation_state fields have
   * been overridden, those overrides will be deleted in order to revert the
   * fields and a message will be displayed reminding the user to remove those
   * fields from their exported config.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   */
  public function completeMigration($entity_type_id) {
    $storage = $this->entityTypeManager->getStorage('base_field_override');

    $overridden_fields = $storage->getQuery()
      ->condition('entity_type', $entity_type_id)
      ->condition('field_name', [
        'scheduled_publication',
        'scheduled_moderation_state',
      ])
      ->execute();

    if ($overridden_fields) {
      $overridden_fields = $storage->loadMultiple($overridden_fields);
      $storage->delete($overridden_fields);

      $message = $this->t('Overridden scheduled_publication and scheduled_moderation_state fields were detected. They have been reverted, but you must remember to remove them from your exported config.');
      $this->messenger->addWarning($message);
    }

    // Base fields have been reverted, and the migration is now complete.
    $migrations = array_diff($this->getMigrations(), [$entity_type_id]);
    $this->setMigrations($migrations);
  }

  /**
   * Generates an informational message to be displayed before starting the
   * migration for a set of entity types.
   *
   * @param EntityTypeInterface[] $entity_types
   *   The entity types that will be migrated.
   * @param bool $html
   *   Whether to include HTML tags in the message.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The generated message.
   */
  public function generatePreMigrationMessage(array $entity_types, $html = TRUE) {
    $message = 'You are about to migrate scheduled transitions for all @entity_types. This will modify your existing content and may take a long time if you have a huge site with tens of thousands of pieces of content. <strong>You cannot undo this</strong>, so you may want to <strong>back up your database</strong> and <a href=":maintenance_mode">switch to maintenance mode</a> before continuing.';
    if ($html == FALSE) {
      $message = strip_tags($message);
    }

    $variables = [
      '@entity_types' =>
        Element::oxford($this->entityTypeOptions($entity_types)),
      ':maintenance_mode' =>
        Url::fromRoute('system.site_maintenance_mode')->toString(),
    ];
    return $this->t($message, $variables);
  }

  /**
   * Converts a set of entity type definitions to key/value options.
   *
   * @param EntityTypeInterface[] $entity_types
   *   The entity type definitions.
   *
   * @return string[]
   *   The entity type labels, keyed by entity type ID.
   */
  public function entityTypeOptions(array $entity_types) {
    $to_option = function (EntityTypeInterface $entity_type) {
      return $entity_type->getPluralLabel();
    };

    return array_map($to_option, $entity_types);
  }

}
