<?php

namespace Drupal\Tests\migrate_plus\Kernel;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\Tests\migrate\Kernel\MigrateTestBase;

/**
 * Test migration config entity discovery.
 *
 * @group migrate_plus
 */
class MigrationConfigEntityTest extends MigrateTestBase {

  public static $modules = [
    'migrate',
    'migrate_plus',
    'migrate_plus_test',
    'taxonomy',
    'text',
    'system',
  ];

  /**
   * The plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManager
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->pluginManager = \Drupal::service('plugin.manager.migration');
    $this->installConfig('migrate_plus');
    $this->installEntitySchema('taxonomy_term');
    $this->installSchema('system', ['key_value', 'key_value_expire']);
  }

  /**
   * Tests cache invalidation.
   */
  public function testCacheInvalidation() {
    $config = Migration::create([
      'id' => 'test',
      'status' => TRUE,
      'label' => 'Label A',
      'migration_tags' => [],
      'source' => [],
      'destination' => [],
      'migration_dependencies' => [],
    ]);
    $config->save();

    $this->assertTrue($this->pluginManager->getDefinition('test'));
    $this->assertSame('Label A', $this->pluginManager->getDefinition('test')['label']);

    // Clear static cache in the plugin manager, the cache tag take care of the
    // persistent cache.
    $this->pluginManager->useCaches(FALSE);
    $this->pluginManager->useCaches(TRUE);

    $config->set('label', 'Label B');
    $config->save();

    $this->assertSame('Label B', $this->pluginManager->getDefinition('test')['label']);
  }

  /**
   * Tests migration status.
   */
  public function testMigrationStatus() {
    $configs = [
      [
        'id' => 'test_active',
        'status' => TRUE,
        'label' => 'Label Active',
        'migration_tags' => [],
        'source' => [],
        'destination' => [],
        'migration_dependencies' => [],
      ],
      [
        'id' => 'test_inactive',
        'status' => FALSE,
        'label' => 'Label Inactive',
        'migration_tags' => [],
        'source' => [],
        'destination' => [],
        'migration_dependencies' => [],
      ],
    ];

    foreach ($configs as $config) {
      Migration::create($config)->save();
    }

    $definitions = $this->pluginManager->getDefinitions();
    $this->assertCount(1, $definitions);
    $this->assertArrayHasKey('test_active', $definitions);

    $this->setExpectedException(PluginNotFoundException::class, 'The "test_inactive" plugin does not exist.');
    $this->pluginManager->getDefinition('test_inactive');
  }

  /**
   * Tests migration from configuration.
   */
  public function testImport() {
    $this->installConfig('migrate_plus_test');
    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $this->pluginManager->createInstance('fruit_terms');
    $id_map = $migration->getIdMap();
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();
    $this->assertSame(3, $id_map->importedCount());
  }

}
