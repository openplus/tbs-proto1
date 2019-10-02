<?php

namespace Drupal\Tests\lightning_layout\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\lightning_layout\Traits\PanelsIPETrait;

/**
 * @group lightning_layout
 */
class PanelizerWizardTest extends WebDriverTestBase {

  use PanelsIPETrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_landing_page',
    'views',
  ];

  /**
   * Saving a panelized entity should not affect blocks placed via IPE.
   */
  public function testBlockPlacement() {
    $user = $this->createUser([], NULL, TRUE);
    $page = $this->createNode([
      'type' => 'landing_page',
      'uid' => $user->id(),
    ]);

    $this->drupalLogin($user);
    $this->drupalGet($page->toUrl());

    // Add a Who's online block to the page.
    $this->getBlockForm('views_block:who_s_online-who_s_online_block', 'Lists (Views)')->pressButton('Add');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->saveLayout();
    $this->assertSession()->elementExists('css', '[data-block-plugin-id="views_block:who_s_online-who_s_online_block"]');

    // Save the page via edit form, assert the block is still there.
    $this->drupalGet($page->toUrl('edit-form'));
    $this->assertSession()->buttonExists('Save')->click();
    $this->assertSession()->elementExists('css', '[data-block-plugin-id="views_block:who_s_online-who_s_online_block"]');
  }

}
