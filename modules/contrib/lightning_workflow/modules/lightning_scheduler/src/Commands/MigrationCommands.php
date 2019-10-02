<?php

namespace Drupal\lightning_scheduler\Commands;

use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_scheduler\Migrator;
use Drush\Commands\DrushCommands;

/**
 * Provides Drush commands for migrating scheduler data to the new base fields.
 *
 * This class is final for the same reason that the Migrator service is: the
 * migration is not an API and should not be extended or re-used.
 */
final class MigrationCommands extends DrushCommands {

  /**
   * The migrator service.
   *
   * @var \Drupal\lightning_scheduler\Migrator
   */
  protected $migrator;

  /**
   * LightningSchedulerCommands constructor.
   *
   * @param \Drupal\lightning_scheduler\Migrator $migrator
   *   The migrator service.
   */
  public function __construct(Migrator $migrator, TranslationInterface $translation = NULL) {
    $this->migrator = $migrator;
  }

  /**
   * Migrates scheduled transition data to the new base fields.
   *
   * @param $entity_type_id
   *   (optional) The entity type ID to migrate.
   *
   * @command lightning:scheduler:migrate
   */
  public function migrate($entity_type_id = NULL) {
    $out = $this->output();

    $entity_types = $this->migrator->getEntityTypesToMigrate((array) $entity_type_id);
    if (empty($entity_types)) {
      if ($entity_type_id) {
        $out->writeln("The $entity_type_id entity type does not need to be migrated.");
      }
      else {
        $out->writeln('All migrations are complete.');
      }
      return;
    }

    $message = $this->migrator->generatePreMigrationMessage($entity_types, FALSE);
    $out->writeln((string) $message);

    $continue = $this->confirm('Continue?');
    if (empty($continue)) {
      return;
    }

    foreach ($entity_types as $entity_type_id => $entity_type) {
      // Create a progress callback which will output a message for every ten
      // items migrated.
      $callback = function ($entity_type_id, $count) use ($entity_type, $out) {
        if ($count % 10 === 0) {
          $variables = [
            '@singular' => $entity_type->getSingularLabel(),
            '@plural' => $entity_type->getPluralLabel(),
          ];
          $message = new PluralTranslatableMarkup($count, '1 @singular migrated.', '@count @plural migrated.', $variables);

          $out->writeln((string) $message);
        }
      };
      $this->migrator->migrateAll($entity_type_id, $callback);
    }
  }

  /**
   * Deletes old scheduled transition data for an entity type without migrating.
   *
   * @param $entity_type_id
   *   The entity type ID to migrate.
   *
   * @command lightning:scheduler:purge
   */
  public function purge($entity_type_id) {
    $out = $this->output();

    $entity_types = $this->migrator->getEntityTypesToMigrate((array) $entity_type_id);
    if (empty($entity_types)) {
      $out->writeln('The given entity type either does not need to be migrated, or it has already been migrated or purged.');
      return;
    }

    $message = "You are about to purge existing scheduled transitions for the given entity type. This will permanently delete scheduled transitions and cannot be undone.";
    $out->writeln($message);

    $continue = $this->confirm('Continue?');
    if (empty($continue)) {
      return;
    }

    $this->migrator->purge($entity_type_id, 'scheduled_publication');
    $this->migrator->purge($entity_type_id, 'scheduled_moderation_state');
    $this->migrator->completeMigration($entity_type_id);
  }

}
