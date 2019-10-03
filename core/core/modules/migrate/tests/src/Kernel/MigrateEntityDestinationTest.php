<?php

namespace Drupal\Tests\migrate\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;

/**
 * Tests the destination Entity plugin.
 *
 * @group migrate
 */
class MigrateEntityDestinationTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   *
   * @todo: Remove migrate_drupal when https://www.drupal.org/node/2560795 is
   * fixed.
   */
  public static $modules = [
    'system',
    'user',
    'node',
    'field',
    'migrate_drupal',
    'migrate_destination_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $type = NodeType::create([
      'type' => 'test_node_type',
      'name' => 'Test node type',
    ]);
    $type->save();

    $type = NodeType::create([
      'type' => 'test_node_type_2',
      'name' => 'Test node type 2',
    ]);
    $type->save();
  }

  /**
   * Test destination fields() method.
   */
  public function testDestinationField() {
    $migration = $this->getMigration('destination_entity');
    $destination = $migration->getDestinationPlugin();

    $migration2 = $this->getMigration('destination_bundle_entity');
    $destination2 = $migration2->getDestinationPlugin();

    $this->assertTrue(in_array('nid', array_keys($destination->fields())));
    $this->assertFalse(in_array('field_text', array_keys($destination->fields())));

    $this->assertTrue(in_array('nid', array_keys($destination2->fields())));
    $this->assertFalse(in_array('field_text', array_keys($destination2->fields())));

    // Create a text field attached to 'test_node_type_2' node-type.
    FieldStorageConfig::create([
      'type' => 'string',
      'entity_type' => 'node',
      'field_name' => 'field_text',
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'test_node_type_2',
      'field_name' => 'field_text',
    ])->save();

    $this->assertTrue(in_array('field_text', array_keys($destination->fields())));
    // The destination_bundle_entity migration has default bundle of
    // test_node_type so it shouldn't show the fields on other node types.
    $this->assertFalse(in_array('field_text', array_keys($destination2->fields())));

  }

}
