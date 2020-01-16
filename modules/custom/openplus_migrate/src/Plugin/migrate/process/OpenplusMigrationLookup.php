<?php

namespace Drupal\openplus_migrate\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Looks up the value of a property based on a previous migration.
 *
 * It is important to maintain relationships among content coming from the
 * source site. For example, on the source site, a given user account may
 * have an ID of 123, but the Drupal user account created from it may have
 * a uid of 456. The migration process maintains the relationships between
 * source and destination identifiers in map tables, and this information
 * is leveraged by the migration_lookup process plugin.
 *
 * Available configuration keys
 * - migration: A single migration ID, or an array of migration IDs.
 * - source_ids: (optional) An array keyed by migration IDs with values that are
 *   a list of source properties.
 * - stub_id: (optional) Identifies the migration which will be used to create
 *   any stub entities.
 * - no_stub: (optional) Prevents the creation of a stub entity when no
 *   relationship is found in the migration map.
 *
 *
 * If the source value passed in to the plugin is NULL, boolean FALSE, an empty
 * array or an empty string, the plugin will throw a
 * MigrateSkipProcessException, causing further plugins in the process to be
 * skipped.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "op_migration_lookup"
 * )
 */
class OpenplusMigrationLookup extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $migrationPluginManager;

  /**
   * The migration to be executed.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * Constructs a MigrationLookup object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The Migration the plugin is being used in.
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   *   The Migration Plugin Manager Interface.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, MigrationPluginManagerInterface $migration_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrationPluginManager = $migration_plugin_manager;
    $this->migration = $migration;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('plugin.manager.migration')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\migrate\MigrateSkipProcessException
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $lookup_migrations_ids = $this->configuration['migration'];
    if (!is_array($lookup_migrations_ids)) {
      $lookup_migrations_ids = [$lookup_migrations_ids];
    }
    $self = FALSE;
    /** @var \Drupal\migrate\Plugin\MigrationInterface[] $lookup_migrations */
    $destination_ids = NULL;
    $source_id_values = [];

    $lookup_migrations = $this->migrationPluginManager->createInstances($lookup_migrations_ids);
    foreach ($lookup_migrations as $lookup_migration_id => $lookup_migration) {
      if ($lookup_migration_id == $this->migration->id()) {
        $self = TRUE;
      }
      if (isset($this->configuration['source_ids'][$lookup_migration_id])) {
        $value = array_values($row->getMultiple($this->configuration['source_ids'][$lookup_migration_id]));
      }
      if (!is_array($value)) {
        $value = [$value];
      }
      $this->skipInvalid($value);
      $source_id_values[$lookup_migration_id] = $value;
      // Break out of the loop as soon as a destination ID is found.
      if ($destination_ids = $lookup_migration->getIdMap()->lookupDestinationId($source_id_values[$lookup_migration_id])) {
        break;
      }
    }

    if (!$destination_ids && !empty($this->configuration['no_stub'])) {
      return NULL;
    }

    if ($destination_ids) {
      if (count($destination_ids) == 1) {
        return reset($destination_ids);
      }
      else {
        return $destination_ids;
      }
    }
  }

  /**
   * Skips the migration process entirely if the value is invalid.
   *
   * @param array $value
   *   The incoming value to check.
   *
   * @throws \Drupal\migrate\MigrateSkipProcessException
   */
  protected function skipInvalid(array $value) {
    if (!array_filter($value, [$this, 'isValid'])) {
      throw new MigrateSkipProcessException();
    }
  }

  /**
   * Determines if the value is valid for lookup.
   *
   * The only values considered invalid are: NULL, FALSE, [] and "".
   *
   * @param string $value
   *   The value to test.
   *
   * @return bool
   *   Return true if the value is valid.
   */
  protected function isValid($value) {
    return !in_array($value, [NULL, FALSE, [], ""], TRUE);
  }

  /**
   * Create a stub row source for later import as stub data.
   *
   * This simple wrapper of the Row constructor allows sub-classing plugins to
   * have more control over the row.
   *
   * @param array $values
   *   An array of values to add as properties on the object.
   * @param array $source_ids
   *   An array containing the IDs of the source using the keys as the field
   *   names.
   *
   * @return \Drupal\migrate\Row
   *   The stub row.
   */
  protected function createStubRow(array $values, array $source_ids) {
    return new Row($values, $source_ids, TRUE);
  }

}
