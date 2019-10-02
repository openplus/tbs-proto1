<?php

namespace Drupal\Tests\lightning_layout\FunctionalJavascript;

use Drupal\block_content\Entity\BlockContent;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\lightning_layout\Traits\PanelsIPETrait;

/**
 * @group lightning_layout
 */
class PanelizerTest extends WebDriverTestBase {

  use PanelsIPETrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block_content_test',
    'lightning_landing_page',
    'lightning_roles',
    'views',
  ];

  public function testPlaceBlockInNonDefaultDisplay() {
    $account = $this->drupalCreateUser();
    $account->addRole('landing_page_creator');
    $account->save();
    $this->drupalLogin($account);

    $page = $this->drupalCreateNode(['type' => 'landing_page']);

    $block = BlockContent::create([
      'type' => 'basic',
      'info' => $this->randomString(),
      'body' => $this->getRandomGenerator()->paragraphs(),
    ]);
    $this->assertSame(SAVED_NEW, $block->save());

    $this->drupalGet($page->toUrl('edit-form'));
    $this->assertSession()->selectExists('Full content')->selectOption('two_column');
    $this->assertSession()->buttonExists('Save')->press();

    $plugin_id = 'block_content:' . $block->uuid();

    $this->getBlockForm($plugin_id, 'Custom')->pressButton('Add');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()
      ->elementExists('named', ['link', 'Save'], $this->getTray())
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->drupalGet($page->toUrl('edit-form'));
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->elementExists('css', "[data-block-plugin-id='$plugin_id']");
  }

  /**
   * Tests that layouts can be edited in isolation.
   */
  public function testEditIsolation() {
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $alpha = $this->drupalCreateNode(['type' => 'landing_page']);
    $beta = $this->drupalCreateNode(['type' => 'landing_page']);

    $block_selector = '[data-block-plugin-id="views_block:who_s_online-who_s_online_block"]';

    $this->drupalGet($alpha->toUrl());
    $this->getBlockForm('views_block:who_s_online-who_s_online_block', 'Lists (Views)')
      ->pressButton('Add');
    $this->assertSession()->waitForElement('css', $block_selector);

    // Changes to Alpha's layout should not affect Beta.
    $this->drupalGet($beta->toUrl());
    $this->assertSession()->elementNotExists('css', $block_selector);
  }

  public function testResave() {
    $account = $this->drupalCreateUser([
      'create landing_page content',
      'edit own landing_page content',
      'access panels in-place editing',
      'administer panelizer node landing_page content',
      // This permission is needed to access the whos_online view.
      'access user profiles',
    ]);
    $this->drupalLogin($account);

    $block_selector = '[data-block-plugin-id="views_block:who_s_online-who_s_online_block"]';

    $node = $this->drupalCreateNode([
      'type' => 'landing_page',
      'uid' => $account->id(),
    ]);
    $this->drupalGet($node->toUrl());
    // Panels IPE is enabled...
    $this->assertSession()->elementExists('css', '#panels-ipe-content');
    // ...and standard fields are not present on the default layout.
    $this->assertSession()->elementNotExists('css', '.field--name-uid');
    $this->assertSession()->elementNotExists('css', '.field--name-created');

    // Place the "Who's online" block into the layout and save it as a custom
    // override.
    $this->getBlockForm('views_block:who_s_online-who_s_online_block', 'Lists (Views)')
      ->pressButton('Add');
    $this->assertNotEmpty($this->assertSession()->waitForElement('css', $block_selector));
    $this->getTray()->clickLink('Save');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Edit the node, verify that the layout is a custom override, and re-save.
    $this->drupalGet($node->toUrl('edit-form'));
    $this->assertTrue($this->assertSession()->selectExists('Full content')->hasAttribute('disabled'));
    $this->assertSession()->buttonExists('Save')->press();

    // The "Who's online" block should still be there.
    $this->assertSession()->elementExists('css', $block_selector);
  }

}
