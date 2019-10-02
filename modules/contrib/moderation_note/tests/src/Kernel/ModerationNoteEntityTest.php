<?php

namespace Drupal\Tests\moderation_note\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\moderation_note\Entity\ModerationNote;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\user\RoleInterface;

/**
 * Tests the moderation note entity.
 *
 * @group moderation_note
 */
class ModerationNoteEntityTest extends EntityKernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['moderation_note', 'node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('moderation_note');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node']);
    $type = NodeType::create([
      'type' => 'article',
    ]);
    $type->save();

    // Clear permissions for authenticated users.
    $this->config('user.role.' . RoleInterface::AUTHENTICATED_ID)
      ->set('permissions', [])
      ->save();
    // Create user 1.
    $this->createUser();
  }

  /**
   * Tests that behavior related to the moderated entity works.
   */
  public function testModeratedEntity() {
    $user = $this->createUser([], [
      'access moderation notes',
    ]);
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomString(),
    ]);
    $node->save();
    $note = ModerationNote::create([
      'uid' => $user->id(),
      'entity_type' => $node->getEntityTypeId(),
      'entity_id' => $node->id(),
      'entity_field_name' => 'body',
      'entity_langcode' => 'en',
      'entity_view_mode_id' => 'full',
      'quote' => '',
      'quote_offset' => '',
      'text' => $this->randomString(),
    ]);
    $note->save();
    $this->assertEquals($node->id(), $note->getModeratedEntity()->id(), 'A note can load its moderated entity correctly');
    $node->delete();
    $this->assertFalse(ModerationNote::load($note->id()), 'Notes are deleted when their moderated entity is deleted');
  }

  /**
   * Tests that behavior related to parent notes.
   */
  public function testParentNote() {
    $user = $this->createUser([], [
      'access moderation notes',
    ]);
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomString(),
    ]);
    $node->save();
    $note = ModerationNote::create([
      'uid' => $user->id(),
      'entity_type' => $node->getEntityTypeId(),
      'entity_id' => $node->id(),
      'entity_field_name' => 'body',
      'entity_langcode' => 'en',
      'entity_view_mode_id' => 'full',
      'quote' => '',
      'quote_offset' => '',
      'text' => $this->randomString(),
    ]);
    $note->save();
    for ($i = 0; $i < 5; ++$i) {
      $child = ModerationNote::create([
        'uid' => $user->id(),
        'entity_type' => $node->getEntityTypeId(),
        'entity_id' => $node->id(),
        'entity_field_name' => 'body',
        'entity_langcode' => 'en',
        'entity_view_mode_id' => 'full',
        'quote' => '',
        'quote_offset' => '',
        'text' => $this->randomString(),
        'parent' => $note,
      ]);
      $child->save();
    }
    $this->assertEquals(5, count($note->getChildren()), 'Child notes can be created properly');
    $note->delete();
    $this->assertEmpty(ModerationNote::loadMultiple(), 'Child notes are deleted when their parent is deleted');
  }

  /**
   * Tests that access to notes is valid.
   */
  public function testAccess() {
    $auth_user = $this->createUser();
    $note_only_user = $this->createUser([], [
      'access moderation notes',
    ]);
    $user = $this->createUser([], [
      'access moderation notes',
      'access content',
    ]);
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomString(),
    ]);
    $node->save();
    $note = ModerationNote::create([
      'uid' => $user->id(),
      'entity_type' => $node->getEntityTypeId(),
      'entity_id' => $node->id(),
      'entity_field_name' => 'body',
      'entity_langcode' => 'en',
      'entity_view_mode_id' => 'full',
      'quote' => '',
      'quote_offset' => '',
      'text' => $this->randomString(),
    ]);
    $note->save();
    $this->assertFalse($note->access('view', $auth_user));
    $this->assertFalse($note->access('view', $note_only_user));
    $this->assertTrue($note->access('view', $user));
  }

}
