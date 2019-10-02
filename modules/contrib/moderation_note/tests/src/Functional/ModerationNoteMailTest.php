<?php

namespace Drupal\Tests\moderation_note\Functional;

use Drupal\Core\Test\AssertMailTrait;
use Drupal\moderation_note\Entity\ModerationNote;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests moderation note email notifications.
 *
 * @group moderation_note
 */
class ModerationNoteMailTest extends BrowserTestBase {

  use AssertMailTrait;

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
    $type = NodeType::create([
      'type' => 'article',
    ]);
    $type->save();
  }

  /**
   * Tests that emails send correctly.
   */
  public function testEmail() {
    $dog = $this->createUser(['access moderation notes'], 'dog');
    $cat = $this->createUser(['access moderation notes'], 'cat');
    $rabbit = $this->createUser(['access moderation notes'], 'rabbit');
    $node = Node::create([
      'type' => 'article',
      'title' => 'Chasing squirrels',
    ]);
    $node->save();
    $note = ModerationNote::create([
      'uid' => $dog->id(),
      'entity_type' => $node->getEntityTypeId(),
      'entity_id' => $node->id(),
      'entity_field_name' => 'body',
      'entity_langcode' => 'en',
      'entity_view_mode_id' => 'full',
      'quote' => '',
      'quote_offset' => '',
      'text' => 'This should mention dogs more',
    ]);
    $note->save();
    $this->assertMail('subject', 'Note on "Chasing squirrels"');
    $this->assertMail('to', '"dog" <dog@example.com>');
    $this->assertMailPattern('body', 'This should mention dogs more');
    // Ensure that content authors are notified.
    $node->setOwner($cat);
    $node->save();
    $note = ModerationNote::create([
      'uid' => $dog->id(),
      'entity_type' => $node->getEntityTypeId(),
      'entity_id' => $node->id(),
      'entity_field_name' => 'body',
      'entity_langcode' => 'en',
      'entity_view_mode_id' => 'full',
      'quote' => '',
      'quote_offset' => '',
      'text' => 'Really too much cat content here',
    ]);
    $note->save();
    $this->assertMail('subject', 'Note on "Chasing squirrels"');
    $this->assertMail('to', '"dog" <dog@example.com>, "cat" <cat@example.com>');
    $this->assertMailPattern('body', 'Really too much cat content here');
    // Create a reply as another user.
    $reply = ModerationNote::create([
      'uid' => $rabbit->id(),
      'entity_type' => $node->getEntityTypeId(),
      'entity_id' => $node->id(),
      'parent' => $note,
      'entity_field_name' => 'body',
      'entity_langcode' => 'en',
      'entity_view_mode_id' => 'full',
      'quote' => '',
      'quote_offset' => '',
      'text' => 'I agree!',
    ]);
    $reply->save();
    $this->assertMail('subject', 'Reply on "Chasing squirrels"');
    $this->assertMail('to', '"rabbit" <rabbit@example.com>, "cat" <cat@example.com>, "dog" <dog@example.com>');
    $this->assertMailPattern('body', 'I agree!');
    // Assign the reply.
    $reply->setAssignee($cat);
    $reply->save();
    $this->assertMail('subject', 'Note assigned to you on "Chasing squirrels"');
    $this->assertMail('to', '"cat" <cat@example.com>');
    $this->assertMailPattern('body', 'I agree!');
    // Resolve the parent note.
    $note->setUnpublished();
    $note->save();
    $this->assertMail('subject', 'Note resolved on "Chasing squirrels"');
    $this->assertMail('to', '"dog" <dog@example.com>, "cat" <cat@example.com>, "rabbit" <rabbit@example.com>');
    $this->assertMailPattern('body', 'Really too much cat content here');
    // Make sure no extra mail was sent.
    $this->assertCount(5, $this->getMails());
  }

}
