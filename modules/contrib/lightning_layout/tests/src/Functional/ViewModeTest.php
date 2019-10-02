<?php

namespace Drupal\Tests\lightning_layout\Functional;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * @group lightning_layout
 */
class ViewModeTest extends BrowserTestBase {

  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_landing_page',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Tests that internal view displays show a warning when being edited.
   */
  public function testInternalWarning() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $account = $this->drupalCreateUser([
      'administer display modes',
      'administer node display',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/structure/display-modes/view');
    $assert_session->statusCodeEquals(200);
    $page->clickLink('Add new Content view mode');
    $page->fillField('Name', 'Foobaz');
    $page->fillField('id', 'foobaz');
    $page->pressButton('Save');
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextContains('Foobaz');

    $view_mode = EntityViewMode::load('node.foobaz');
    $this->assertInstanceOf(EntityViewModeInterface::class, $view_mode);
    /** @var EntityViewModeInterface $view_mode */
    $view_mode->setThirdPartySetting('lightning_core', 'internal', TRUE);
    $this->assertSame(SAVED_UPDATED, $view_mode->save());

    $this->drupalGet("/admin/structure/types/manage/landing_page/display");
    $assert_session->statusCodeEquals(200);
    $page->checkField('Foobaz');
    $page->pressButton('Save');
    $page->clickLink('Foobaz');
    $assert_session->pageTextContains('This display is internal and will not be seen by normal users.');
    $assert_session->fieldNotExists('Panelize this view mode');
  }

  /**
   * Tests that the Panelizer field widget shows view mode descriptions.
   */
  public function testDescription() {
    $view_mode = EntityViewMode::load('node.full');
    $this->assertInstanceOf(EntityViewMode::class, $view_mode);

    $description = $this->getRandomGenerator()->sentences(4);
    $view_mode->setThirdPartySetting('lightning_core', 'description', $description);
    $this->assertSame(SAVED_UPDATED, $view_mode->save());

    $account = $this->drupalCreateUser([
      'create landing_page content',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/node/add/landing_page');
    $this->assertSession()->pageTextContains($description);
  }

  /**
   * Tests that the Panelizer widget respects view mode customization settings.
   */
  public function testCustomization() {
    $assert_session = $this->assertSession();

    $display = entity_get_display('node', 'landing_page', 'search_result')
      ->setStatus(TRUE)
      ->setThirdPartySetting('panelizer', 'enable', TRUE)
      ->setThirdPartySetting('panelizer', 'custom', TRUE)
      ->setThirdPartySetting('panelizer', 'allow', TRUE);

    $this->assertSame(SAVED_NEW, $display->save());

    $account = $this->drupalCreateUser([
      'create landing_page content',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('/node/add/landing_page');
    $assert_session->fieldExists('Full content');
    $assert_session->fieldExists('Search result highlighting input');

    $this->assertSame(SAVED_UPDATED, $display->setStatus(FALSE)->save());
    $this->getSession()->reload();
    $assert_session->fieldExists('Full content');
    $assert_session->fieldNotExists('Search result highlighting input');
  }

}
