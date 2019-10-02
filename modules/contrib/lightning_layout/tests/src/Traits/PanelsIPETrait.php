<?php

namespace Drupal\Tests\lightning_layout\Traits;

trait PanelsIPETrait {

  /**
   * Saves the Panels IPE layout.
   */
  protected function saveLayout() {
    $this->assertSession()->elementExists('named', ['link', 'Save'], $this->getTray())->click();
    $this->assertSession()->waitForElement('css', '#panels-ipe-tray:not(.unsaved)');
  }

  /**
   * Opens the form to add a block to the Panels IPE layout.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param string $category
   *   The category of the block.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The block form.
   */
  protected function getBlockForm($plugin_id, $category) {
    $assert = $this->assertSession();

    $tab = $this->getTab('Manage Content');

    $category = $assert->elementExists('css', '.ipe-category[data-category="' . $category . '"]', $tab);
    if (! $category->hasClass('active')) {
      $category->click();
      $assert->assertWaitOnAjaxRequest();
    }

    $assert->elementExists('css', '.ipe-block-plugin a[data-plugin-id="' . $plugin_id . '"]', $tab)->click();
    $block_form = $assert->waitForElementVisible('css', '.panels-ipe-block-plugin-form');
    $this->assertNotEmpty($block_form);

    return $block_form;
  }

  /**
   * Opens a tab in the Panels IPE tray and returns its contents.
   *
   * @param string $label
   *   The label of the tab.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The tab contents.
   */
  protected function getTab($label) {
    $tray = $this->getTray();

    $tray->clickLink($label);

    $assert = $this->assertSession();
    $assert->assertWaitOnAjaxRequest();

    return $assert->elementExists('css', '.ipe-tabs-content', $tray);
  }

  /**
   * Returns the Panels IPE tray.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The tray element.
   */
  protected function getTray() {
    return $this->assertSession()
      ->elementExists('css', '#panels-ipe-tray');
  }

}
