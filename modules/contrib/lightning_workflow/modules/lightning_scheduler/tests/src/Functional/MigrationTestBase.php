<?php

namespace Drupal\Tests\lightning_scheduler\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

abstract class MigrationTestBase extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../fixtures/BaseFieldMigrationTest.php.gz',
    ];
  }

  public function test() {
    // Forcibly uninstall Lightning Dev. It is mentioned in the fixture, but not
    // physically present in ORCA fixtures.
    $this->config('core.extension')
      ->clear('module.lightning_dev')
      ->save();
    $this->container
      ->get('keyvalue')
      ->get('system.schema')
      ->deleteMultiple(['lightning_dev']);

    $this->runUpdates();

    $migrations = $this->container->get('state')->get('lightning_scheduler.migrations');
    $this->assertCount(2, $migrations);
    $this->assertContains('block_content', $migrations);
    $this->assertContains('node', $migrations);

    $assert = $this->assertSession();
    $url = $assert->elementExists('named', ['link', 'migrate your existing content'])->getAttribute('href');

    $this->drupalLogin($this->rootUser);
    $this->drupalGet($url);
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Migrate scheduled transitions');
    $assert->elementExists('named', ['link', 'switch to maintenance mode']);
  }

  /**
   * Runs post-migration assertions for an entity type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The storage handler for the entity type.
   */
  protected function postMigration($entity_type_id) {
    // Now that a migration is completed, old base fields will no longer be
    // defined. Therefore, we need to clear the entity field cache in order to
    // properly load the changed content, and there should be pending entity
    // definition updates (the old base fields need to be uninstalled).
    $this->container->get('entity_field.manager')->clearCachedFieldDefinitions();

    $this->assertTrue(
      $this->container->get('entity.definition_update_manager')->needsUpdates()
    );

    return $this->container->get('entity_type.manager')->getStorage($entity_type_id);
  }

}
