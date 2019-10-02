<?php

namespace Drupal\Tests\lightning_workflow\ExistingSite;

use Drupal\Tests\lightning_workflow\FixtureContext;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * @group lightning
 * @group lightning_workflow
 */
class ContentTypeModerationTest extends ExistingSiteBase {

  use ContentTypeCreationTrait;

  /**
   * The fixture context.
   *
   * @var \Drupal\Tests\lightning_workflow\FixtureContext
   */
  private $fixtureContext;

  /**
   * The content type created during the test.
   *
   * @var \Drupal\node\NodeTypeInterface
   */
  private $nodeType;

  /**
   * {@inheritdoc}
   */
  protected function prepareRequest() {
    // The base implementation of this method will set a special cookie
    // identifying the Mink session as a test user agent. For this kind of test,
    // though, we don't need that.
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->fixtureContext = new FixtureContext($this->container);
    $this->fixtureContext->setUp();
    drupal_flush_all_caches();
    $this->nodeType = $this->createContentType();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->nodeType->delete();
    $this->fixtureContext->tearDown();
    parent::tearDown();
  }

  /**
   * Tests access to unpublished content.
   */
  public function testUnpublishedAccess() {
    $this->enableModeration();

    $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Moderation Test 1',
      'promote' => TRUE,
      'moderation_state' => 'review',
    ]);
    $this->drupalGet('');
    $this->assertSession()->linkNotExists('Moderation Test 1');

    $account = $this->createUser([
      'access content overview',
      'view any unpublished content',
    ]);
    $this->drupalLogin($account);
    $this->drupalGet('/admin/content');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('named', ['link', 'Moderation Test 1'])->click();
    $this->assertSession()->statusCodeEquals(200);
  }

  public function testReviewerAccess() {
    $this->enableModeration();

    $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Version 1',
      'moderation_state' => 'draft',
    ]);

    $account = $this->createUser();
    $account->addRole('page_reviewer');
    $account->save();
    $this->drupalLogin($account);

    $this->drupalGet('/admin/content');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('named', ['link', 'Version 1'])->click();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Version 1');
  }

  /**
   * @depends testReviewerAccess
   */
  public function testLatestUnpublishedRevisionReviewerAccess() {
    $this->enableModeration();

    $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Version 1',
      'moderation_state' => 'draft',
    ]);

    $account = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/content');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('named', ['link', 'Version 1'])->click();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->fieldExists('Title')->setValue('Version 2');
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('published');
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->fieldExists('Title')->setValue('Version 3');
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('draft');
    $this->assertSession()->buttonExists('Save')->press();

    $this->drupalLogout();
    $account = $this->createUser();
    $account->addRole('page_reviewer');
    $account->save();
    $this->drupalLogin($account);

    $this->drupalGet('/admin/content');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('named', ['link', 'Version 2'])->click();
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('named', ['link', 'Latest version']);
  }

  /**
   * Tests that unmoderated content types have a "create new revision" checkbox.
   */
  public function testCreateNewRevisionCheckbox() {
    $account = $this->createUser();
    $account->addRole('administrator');
    $account->save();
    $this->drupalLogin($account);

    $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Deft Zebra',
    ]);
    $this->drupalGet('/admin/content');
    $this->assertSession()->elementExists('named', ['link', 'Deft Zebra'])->click();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->fieldExists('Create new revision');
  }

  /**
   * Tests that moderated content does not provide publish/unpublish buttons.
   */
  public function testEnableModerationForContentType() {
    $account = $this->createUser([
      'administer nodes',
      'create ' . $this->nodeType->id() . ' content',
    ]);
    $this->drupalLogin($account);

    $this->visit('/node/add/' . $this->nodeType->id());
    $this->assertSession()->buttonExists('Save');
    $this->assertSession()->checkboxChecked('Published');
    $this->assertSession()->buttonNotExists('Save and publish');
    $this->assertSession()->buttonNotExists('Save as unpublished');

    $this->enableModeration();

    $this->getSession()->reload();
    $this->assertSession()->buttonExists('Save');
    $this->assertSession()->fieldNotExists('status[value]');
    $this->assertSession()->buttonNotExists('Save and publish');
    $this->assertSession()->buttonNotExists('Save as unpublished');
  }

  /**
   * Tests that moderated content does not have publish/unpublish actions.
   *
   * @depends testEnableModerationForContentType
   */
  public function testContentOverviewActions() {
    $account = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->enableModeration();

    $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Foo',
      'moderation_state' => 'draft',
    ]);
    $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Bar',
      'moderation_state' => 'draft',
    ]);
    $this->createNode([
      'type' => $this->nodeType->id(),
      'title' => 'Baz',
      'moderation_state' => 'draft',
    ]);

    $this->drupalGet('/admin/content');
    $this->assertSession()->selectExists('moderation_state')->selectOption('Draft');

    $this->assertSession()
      ->elementExists('css', '.views-exposed-form .form-actions input[type = "submit"]')
      ->press();

    $this->assertSession()->optionNotExists('Action', 'node_publish_action');
    $this->assertSession()->optionNotExists('Action', 'node_unpublish_action');
  }

  /**
   * Enables moderation for the content type under test.
   */
  private function enableModeration() {
    $this->nodeType->setThirdPartySetting('lightning_workflow', 'workflow', 'editorial');

    $this->container
      ->get('module_handler')
      ->invoke('lightning_workflow', 'node_type_insert', [ $this->nodeType ]);
  }

}
