<?php

namespace Drupal\Tests\lightning_workflow\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * @group lightning_workflow
 */
class QuickEditTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'lightning_page',
    'lightning_workflow',
    'quickedit',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('system_main_block');
  }

  public function testQuickEditDisabledForPublishedContent() {
    $account = $this->drupalCreateUser([
      'use editorial transition publish',
      'view own unpublished content',
      'access in-place editing',
      'access contextual links',
      'view any unpublished content',
      'edit any page content',
    ]);
    $this->drupalLogin($account);

    $node = $this->drupalCreateNode(['type' => 'page']);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('Published');
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->addressMatches('|^/node/[0-9]+$|');
    $this->assertJsCondition('Drupal.quickedit.collections.entities.length === 0');
  }

  public function testQuickEditEnabledForPendingRevisions() {
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $node = $this->drupalCreateNode([
      'type' => 'page',
      'moderation_state' => 'published',
    ]);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('Draft');
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->addressMatches('|^/node/[0-9]+/latest$|');
    $this->assertJsCondition('Drupal.quickedit.collections.entities.length > 0');

    $contextual_links = $this->assertSession()
      ->elementExists('css', 'div[data-block-plugin-id="system_main_block"] ul.contextual-links');
    $this->assertSession()->elementExists('named', ['link', 'Quick edit'], $contextual_links);
  }

}
