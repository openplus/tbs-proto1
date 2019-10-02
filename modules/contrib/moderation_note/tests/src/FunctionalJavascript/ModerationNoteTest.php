<?php

namespace Drupal\Tests\moderation_note\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\moderation_note\Entity\ModerationNote;

/**
 * Contains Moderation Note integration tests.
 *
 * @group moderation_note
 */
class ModerationNoteTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'node', 'moderation_note'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a Content Type with moderation enabled.
    $node_type = $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    /* @var \Drupal\workflows\WorkflowInterface $workflow */
    $workflow = $this->container->get('entity_type.manager')->getStorage('workflow')->load('editorial');
    $workflow->getTypePlugin()->addEntityTypeAndBundle('node', 'article');
    $workflow->save();
    $node_type->setNewRevision(TRUE);
    $node_type->save();

    // Add a plain text field for this content type.
    FieldStorageConfig::create([
      'field_name' => 'test_field',
      'entity_type' => 'node',
      'type' => 'string',
    ])->save();

    FieldConfig::create([
      'field_name' => 'test_field',
      'label' => 'Test Field',
      'entity_type' => 'node',
      'bundle' => 'article',
      'required' => FALSE,
      'settings' => [],
      'description' => '',
    ])->save();

    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $entity_display */
    $entity_display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('node.article.default');
    $entity_display->setComponent('test_field')->save();

    // Create a user who can use Moderation Note.
    $user = $this->drupalCreateUser([
      'access moderation notes',
      'create moderation notes',
      'access content',
      'create article content',
      'edit any article content',
    ]);
    $this->drupalLogin($user);

    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Tests that notating entities is working as expected.
   */
  public function testModerationNote() {
    // Create a new article.
    $node = $this->createNode([
      'type' => 'article',
      'test_field' => [
        'value' => 'This is speled wrong',
      ],
      'moderation_state' => 'published',
    ]);

    // Create a Moderation Note that selects the misspelling.
    /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
    $note = ModerationNote::create([
      'entity_type' => 'node',
      'entity_id' => $node->id(),
      'entity_field_name' => 'test_field',
      'entity_langcode' => 'en',
      'entity_view_mode_id' => 'full',
      'quote' => 'speled',
      'quote_offset' => 0,
      'text' => 'Fix this mistake',
    ]);
    $note->save();

    // Make sure that the highlight is made and contains the misspelling.
    $this->drupalGet('node/' . $node->id());
    $session = $this->assertSession();
    $session->assertWaitOnAjaxRequest();
    $element = $session->elementExists('css', '[data-moderation-note-highlight-id="' . $note->id() . '"]');
    $this->assertEquals($element->getText(), 'speled');

    // Open the note sidebar.
    $view_tooltip = $session->elementExists('css', '.moderation-note-tooltip:contains("View note")');
    $this->assertFalse($view_tooltip->isVisible());
    // This also triggers a hover, which we could do with jQuery.
    $element->click();
    $session->assertWaitOnAjaxRequest();
    $this->assertTrue($view_tooltip->isVisible());
    $view_tooltip->click();
    $session->assertWaitOnAjaxRequest();
    $session->pageTextContains('Fix this mistake');

    // Open the edit form.
    $session->waitForElementVisible('css', '.moderation-note a:contains("Edit")')->click();
    $session->assertWaitOnAjaxRequest();
    $form = $session->elementExists('css', '.moderation-note-form-wrapper:not(.moderation-note-form-reply)');
    $textarea = $form->find('css', '.form-item-text textarea');
    $this->assertEquals($textarea->getValue(), 'Fix this mistake');
    $textarea->setValue('Fix this mistake!!!');
    $form->find('css', 'input[value="Save"]')->click();
    $session->assertWaitOnAjaxRequest();

    // Open the sidebar again.
    $this->assertFalse($view_tooltip->isVisible());
    $element = $session->elementExists('css', '[data-moderation-note-highlight-id="' . $note->id() . '"]');
    $element->click();
    $session->assertWaitOnAjaxRequest();
    $this->assertTrue($view_tooltip->isVisible());
    $view_tooltip->click();
    $session->assertWaitOnAjaxRequest();
    // We should see the last edit from the last form submit.
    $session->pageTextContains('Fix this mistake!!!');

    // Create a reply.
    $form = $session->elementExists('css', '.moderation-note-form-reply');
    $textarea = $form->find('css', '.form-item-text textarea');
    $textarea->setValue("I'm busy");
    $form->find('css', 'input[value="Reply"]')->click();
    $session->assertWaitOnAjaxRequest();
    $session->pageTextContains("I'm busy");
    $this->assertEquals(1, count($note->getChildren()));
    $children = $note->getChildren();
    $this->assertEquals(reset($children)->getText(), "I'm busy");

    // Open the resolve form for the parent note.
    $session->waitForElementVisible('css', '.moderation-note:not(.moderation-note-reply) a:contains("Resolve")')->click();
    $session->assertWaitOnAjaxRequest();
    $session->pageTextContains('Are you sure you want to resolve this note?');
    $form = $session->elementExists('css', '.moderation-note-form-wrapper:not(.moderation-note-form-reply)');
    $form->find('css', 'input[value="Resolve"]')->click();
    $session->assertWaitOnAjaxRequest();
    $session->elementNotExists('css', '[data-moderation-note-highlight-id="' . $note->id() . '"]');

    /** @var \Drupal\moderation_note\ModerationNoteInterface $entity */
    foreach (ModerationNote::loadMultiple() as $entity) {
      $this->assertFalse($entity->isPublished());
    }

    // Refresh the page and delete the resolved note.
    $this->drupalGet('node/' . $node->id());
    $session->waitForElementVisible('css', 'a:contains("View Notes")')->click();
    $session->assertWaitOnAjaxRequest();
    $session->elementExists('css', '.moderation-note-resolved');
    $session->waitForElementVisible('css', 'a:contains("View full note")')->click();
    $session->assertWaitOnAjaxRequest();
    $session->waitForElementVisible('css', '.moderation-note:not(.moderation-note-reply) a:contains("Delete")')->click();
    $session->assertWaitOnAjaxRequest();
    $session->pageTextContains('You are about to delete a note, this action cannot be undone');
    $form = $session->elementExists('css', '.moderation-note-form-wrapper:not(.moderation-note-form-reply)');
    $form->find('css', 'input[value="Delete"]')->click();
    $session->assertWaitOnAjaxRequest();

    // Confirm that both notes were deleted.
    $this->assertEmpty(ModerationNote::loadMultiple());
  }

}
