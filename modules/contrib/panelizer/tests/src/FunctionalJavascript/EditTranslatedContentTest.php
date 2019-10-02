<?php

namespace Drupal\Tests\panelizer\FunctionalJavascript;

use Behat\Mink\Driver\Selenium2Driver;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\panels_ipe\FunctionalJavascript\PanelsIPETestTrait;

/**
 * Tests editing translated content with Panels IPE.
 *
 * @group panelizer
 */
class EditTranslatedContentTest extends JavascriptTestBase {

  use PanelsIPETestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field_ui',
    'node',
    'panels',
    'panels_ipe',
    'panelizer',
    'system',
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create admin user.
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    // Create the "Basic Page" content type.
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic Page',
    ]);

    // Add language.
    $this->drupalGet('admin/config/regional/language/add');
    $this->submitForm(['predefined_langcode' => 'hu'], t('Add language'));

    // Enable Panelizer for the "Basic Page" content type.
    $this->drupalGet('admin/structure/types/manage/page/display');
    $this->submitForm([
      'panelizer[enable]' => 1,
      'panelizer[custom]' => 1,
    ], t('Save'));

    // Enable content translation for the "Basic Page" content type.
    $this->drupalGet('admin/config/regional/content-language');
    $this->submitForm([
      'entity_types[node]' => TRUE,
      'settings[node][page][translatable]' => TRUE,
    ], t('Save configuration'));

    // Create a new Basic Page.
    $this->drupalGet('node/add/page');
    $this->submitForm(['title[0][value]' => 'Test Node'], t('Save'));

    // Translate node.
    $this->drupalGet('node/1/translations/add/en/hu');
    $this->submitForm(['title[0][value]' => 'Test Node -hu-'], t('Save'));
  }

  /**
   * Tests editing translated content with Panels IPE.
   */
  public function testEditingTranslatedContent() {
    // Set the window size to ensure that IPE elements are visible.
    $this->getSession()->resizeWindow(1024, 768);

    // Change the layout of translated node.
    $this->drupalGet('hu/node/1');
    $this->changeLayout('Columns: 2', 'layout_twocol_bricks');
    $this->clickAndWait('[data-tab-id="save"]');

    // Assert original node hasn't changed.
    $this->drupalGet('node/1');
    $this->assertSession()->elementExists('css', '.layout--onecol');
    $this->assertSession()->elementNotExists('css', '.layout--twocol-bricks');

    // Assert Revert to Default is working.
    $driver = $this->getSession()->getDriver();
    if (!($driver instanceof Selenium2Driver)) {
      return;
    }

    $this->drupalGet('hu/node/1');
    $this->assertSession()->elementExists('css', '.layout--twocol-bricks');
    $this->assertSession()->elementNotExists('css', '.layout--onecol');
    $this->click('[data-tab-id="revert"]');
    // Wait for alert box to show up.
    sleep(1);
    $driver->getWebDriverSession()->accept_alert();
    $this->waitForAjaxToFinish();
    $this->assertSession()->elementExists('css', '.layout--onecol');
    $this->assertSession()->elementNotExists('css', '.layout--twocol-bricks');
  }

}
