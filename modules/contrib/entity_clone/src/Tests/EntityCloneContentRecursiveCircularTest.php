<?php

namespace Drupal\entity_clone\Tests;

use Drupal\node\Entity\Node;
use Drupal\node\Tests\NodeTestBase;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;

/**
 * Create a content and test a clone.
 *
 * @group entity_clone
 */
class EntityCloneContentRecursiveCircularTest extends NodeTestBase {

  use EntityReferenceTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['entity_clone', 'block', 'node', 'datetime'];

  /**
   * Profile to install.
   *
   * @var string
   */
  protected $profile = 'standard';

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'bypass node access',
    'administer nodes',
    'clone node entity',
  ];

  /**
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Sets the test up.
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType([
      'type' => 'test_content_type',
      'name' => 'Test content type',
      'display_submitted' => FALSE,
    ]);

    $this->createEntityReferenceField('node', 'test_content_type', 'test_field_reference', 'Test field reference', 'node');

    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test clone a content entity with another entities attached.
   */
  public function testContentEntityClone() {

    $node1_title = $this->randomMachineName(8);
    $node1 = Node::create([
      'type' => 'test_content_type',
      'title' => $node1_title,
    ]);
    $node1->save();

    $node2_title = $this->randomMachineName(8);
    $node2 = Node::create([
      'type' => 'test_content_type',
      'title' => $node2_title,
      'test_field_reference' => $node1,
    ]);
    $node2->save();

    $node1->set('test_field_reference', $node2->id());
    $node1->save();

    $settings = [
      'node' => [
        'default_value' => 1,
        'disable' => 0,
        'hidden' => 0,
      ],
    ];
    \Drupal::service('config.factory')->getEditable('entity_clone.settings')->set('form_settings', $settings)->save();

    $this->drupalPostForm('entity_clone/node/' . $node1->id(), [], t('Clone'));

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => $node1_title . ' - Cloned',
      ]);
    /** @var \Drupal\node\Entity\Node $node1cloned */
    $node1cloned = reset($nodes);
    $this->assertTrue($node1cloned, 'Node 1 cloned found in database.');

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => $node2_title . ' - Cloned',
      ]);
    /** @var \Drupal\node\Entity\Node $node2cloned */
    $node2cloned = reset($nodes);
    $this->assertTrue($node2cloned, 'Node 2 cloned found in database.');

    $reference = $node2cloned->get('test_field_reference')->first()->get('entity')->getTarget()->getValue();
    $this->assertEqual($node1cloned->id(), $reference->id(), "Node 1 reference, from circular reference, is correctly referenced.");

    $node1cloned->delete();
    $node2cloned->delete();

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => $node1_title,
      ]);
    $node = reset($nodes);
    $this->assertTrue($node, 'Test original node 1 found in database.');

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => $node2_title,
      ]);
    $node = reset($nodes);
    $this->assertTrue($node, 'Test original node 2 found in database.');
  }

}
