<?php

namespace Drupal\Tests\lightning_workflow\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * @group lightning_workflow
 */
class ModerationHistoryTest extends BrowserTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_workflow',
    'views',
  ];

  /**
   * The content type created during the test.
   *
   * @var \Drupal\node\NodeTypeInterface
   */
  private $nodeType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->nodeType = $this->createContentType();
  }

  public function testModerationHistory() {
    $user_permissions = [
      'administer nodes',
      'bypass node access',
      'use editorial transition create_new_draft',
      'use editorial transition review',
      'use editorial transition publish',
      'view all revisions',
    ];
    $user_a = $this->createUser($user_permissions, 'userA');
    $user_b = $this->createUser($user_permissions, 'userB');

    $this->enableModeration();

    $node = $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Foo',
      'moderation_state' => 'draft',
    ]);

    // Make two revisions with two different users.
    $timestamp = (new \DateTime())->getTimestamp();
    $timestamp_a = $timestamp + 10;
    $timestamp_b = $timestamp + 20;
    $this->createRevision($node, $user_a->id(), $timestamp_a, 'review');
    $this->createRevision($node, $user_b->id(), $timestamp_b, 'published');

    $this->drupalLogin($user_a);
    $this->drupalGet('/node/' . $node->id() . '/moderation-history');
    $this->assertSession()->statusCodeEquals(200);
    $date_formatter = \Drupal::service('date.formatter');
    $this->assertSession()->pageTextContainsOnce('Set to review on ' . $date_formatter->format($timestamp_a, 'long') . ' by ' . $user_a->getUsername());
    $this->assertSession()->pageTextContainsOnce('Set to published on ' . $date_formatter->format($timestamp_b, 'long') . ' by ' . $user_b->getUsername());
  }

  /**
   * Enables moderation for the content type under test.
   */
  private function enableModeration() {
    $this->nodeType->setThirdPartySetting('lightning_workflow', 'workflow', 'editorial');

    \Drupal::moduleHandler()
      ->invoke('lightning_workflow', 'node_type_insert', [ $this->nodeType ]);
  }

  /**
   * Creates a new revision of the given $node.
   *
   * @param \Drupal\node\NodeInterface $node
   * @param int $user_id
   * @param int $timestamp
   * @param string $state
   * @param string $revision_log
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function createRevision(NodeInterface $node, $user_id, $timestamp, $state, $revision_log = 'Created revision.') {
    $node->setNewRevision();
    $node->setRevisionUserId($user_id);
    $node->setRevisionCreationTime($timestamp);
    $node->revision_log = $revision_log;
    $node->moderation_state = $state;
    $node->save();
  }

}
