<?php

namespace Drupal\Tests\lightning_layout\FunctionalJavascript;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\field\Entity\FieldConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\lightning_layout\Traits\PanelsIPETrait;

/**
 * @group lightning_layout
 * @group orca_public
 */
class IpeQuickEditTest extends WebDriverTestBase {

  use PanelsIPETrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_landing_page',
  ];

  /**
   * Tests quick-editing fields placed in an IPE layout.
   */
  public function testQuickEditInIpe() {
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $page = $this->drupalCreateNode([
      'type' => 'landing_page',
      'body' => [
        [
          'value' => 'How quickly deft jumping zebras vex.',
          'format' => filter_default_format(),
        ],
      ],
    ]);

    $this->drupalLogin($user);
    $this->drupalGet($page->toUrl());

    // Add a body block to the layout.
    $this->getBlockForm('entity_field:node:body', 'Content')->pressButton('Add');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->saveLayout();

    // Assert the quickedit contextual link is present.
    $this->getSession()->reload();
    $link = $this->assertSession()->waitForElement('css', '[data-quickedit-entity-id="node/1"].contextual-region ul.contextual-links li.quickedit');
    $this->assertNotNull($link);
  }

  /**
   * Tests that Quick Edit works with custom blocks created with Panels IPE.
   */
  public function testQuickEditCustomBlock() {
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $block_type = BlockContentType::create([
      'id' => $this->randomMachineName(),
      'label' => $this->randomString(),
    ]);
    $this->assertSame(SAVED_NEW, $block_type->save());

    FieldConfig::create([
      'field_name' => 'body',
      'entity_type' => 'block_content',
      'bundle' => $block_type->id(),
      'label' => 'Body',
    ])->save();

    entity_get_display('block_content', $block_type->id(), 'default')
      ->setComponent('body', [
        'type' => 'text_default',
      ])
      ->save();

    entity_get_form_display('block_content', $block_type->id(), 'default')
      ->setComponent('body', [
        'type' => 'text_textarea_with_summary',
      ])
      ->save();

    /** @var \Drupal\block_content\BlockContentInterface $block */
    $block = BlockContent::create([
      'type' => $block_type->id(),
      'info' => $this->randomString(),
      'body' => $this->getRandomGenerator()->sentences(8),
    ]);
    $this->assertSame(SAVED_NEW, $block->save());
    $this->assertTrue($block->hasField('body'));
    $this->assertFalse($block->get('body')->isEmpty());

    $page = $this->drupalCreateNode([
      'type' => 'landing_page',
    ]);
    $this->drupalGet($page->toUrl());

    $plugin_id = 'block_content:' . $block->uuid();
    $selector = '[data-block-plugin-id="' . $plugin_id . '"]';

    $this->getBlockForm($plugin_id, 'Custom')->pressButton('Add');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->saveLayout();

    // Assert that the block is targeted by Quick Edit.
    $this->assertSession()
      ->elementAttributeContains('css', $selector, 'data-quickedit-entity-id', 'block_content/' . $block->id());

    // Assert that the title and body are displayed, and that Quick Edit is
    // aware of at least one of the fields.
    $element = $this->assertSession()->elementExists('css', $selector);
    $this->assertSession()->elementTextContains('css', $selector, $block->label());
    $this->assertSession()->elementTextContains('css', $selector, $block->body->value);
    $this->assertSession()->elementExists('css', '[data-quickedit-field-id]', $element);

    $this->assertJsCondition('Drupal.quickedit.collections.fields.length > 0');
    $contextual_links = $this->assertSession()->elementExists('css', 'ul.contextual-links', $element);
    $this->assertSession()->elementExists('named', ['link', 'Quick edit'], $contextual_links);
  }

}
