<?php

namespace Drupal\Tests\moderation_sidebar\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Contains Moderation Sidebar integration tests.
 *
 * @group moderation_sidebar
 */
class ModerationSidebarTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'toolbar',
    'moderation_sidebar',
    'node',
    'moderation_sidebar_test',
  ];

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

    // Create a user who can use the Moderation Sidebar.
    $user = $this->drupalCreateUser([
      'access toolbar',
      'use moderation sidebar',
      'access content',
      'create article content',
      'edit any article content',
      'delete any article content',
      'view any unpublished content',
      'view latest version',
      'use editorial transition create_new_draft',
      'use editorial transition publish',
    ]);
    $this->drupalLogin($user);

    drupal_flush_all_caches();
  }

  /**
   * Tests that the Moderation Sidebar is working as expected.
   */
  public function testModerationSidebar() {
    // Create a new article.
    $node = $this->createNode([
      'type' => 'article',
      'moderation_state' => 'published',
    ]);
    $this->drupalGet('node/' . $node->id());

    // Open the moderation sidebar.
    $this->clickLink('Tasks');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Archived transitions should not be visible based on our permissions.
    $this->assertSession()->elementNotExists('css', '.moderation-sidebar-link#published_archived');
    // Create a draft of the article.
    $this->submitForm([], 'Create New Draft');
    $this->assertSession()->addressEquals('node/' . $node->id() . '/latest');

    // Publish the draft.
    $this->clickLink('Tasks');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextNotContains('View existing draft');
    $this->submitForm([], 'Publish');
    $this->assertSession()->addressEquals('node/' . $node->id());

    // Create another draft, then discard it.
    $this->clickLink('Tasks');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->submitForm([], 'Create New Draft');
    $this->assertSession()->addressEquals('node/' . $node->id() . '/latest');
    $this->clickLink('Tasks');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->click('#moderation-sidebar-discard-draft');
    $this->assertSession()->pageTextContains('The draft has been discarded successfully');
  }

}
