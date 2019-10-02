<?php

namespace Drupal\entity_clone\Tests;

use Drupal\node\Entity\Node;
use Drupal\node\Tests\NodeTestBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Create a content and test a clone.
 *
 * @group entity_clone
 */
class EntityCloneContentRecursiveTest extends NodeTestBase {

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

    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test clone a content entity with another entities attached.
   */
  public function testContentEntityClone() {

    $term_title = $this->randomMachineName(8);
    $term = Term::create([
      'vid' => 'tags',
      'name' => $term_title,
    ]);
    $term->save();

    $node_title = $this->randomMachineName(8);
    $node = Node::create([
      'type' => 'article',
      'title' => $node_title,
      'field_tags' => [
        'target_id' => $term->id(),
      ],
    ]);
    $node->save();

    $settings = [
      'taxonomy_term' => [
        'default_value' => 1,
        'disable' => 0,
        'hidden' => 0,
      ],
    ];
    \Drupal::service('config.factory')->getEditable('entity_clone.settings')->set('form_settings', $settings)->save();

    $this->drupalPostForm('entity_clone/node/' . $node->id(), [
      'recursive[node.article.field_tags][references][' . $term->id() . '][clone]' => 1,
    ], t('Clone'));

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => $node_title . ' - Cloned',
      ]);
    /** @var \Drupal\node\Entity\Node $node */
    $node = reset($nodes);
    $this->assertTrue($node, 'Test node cloned found in database.');

    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => $term_title . ' - Cloned',
      ]);
    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = reset($terms);
    $this->assertTrue($term, 'Test term referenced by node cloned too found in database.');

    $node->delete();
    $term->delete();

    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'title' => $node_title,
      ]);
    $node = reset($nodes);
    $this->assertTrue($node, 'Test original node found in database.');

    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => $term_title,
      ]);
    $term = reset($terms);
    $this->assertTrue($term, 'Test original term found in database.');
  }

}
