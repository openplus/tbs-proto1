<?php

namespace Drupal\Tests\library_manager\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the JavaScript functionality of the toolbar.
 *
 * @group library_manager
 */
class LibraryFilterTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   *
   * @see https://www.drupal.org/node/2787529
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['library_manager_test'];

  /**
   * Tests if the library list can be filtered with JavaScript.
   */
  public function testLibraryFilter() {

    $admin_user = $this->drupalCreateUser(['administer libraries']);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/structure/library');

    // Expect at least 15 libraries available.
    $minimal_library_count = 15;
    $this->assertGreaterThan($minimal_library_count, $this->getCountRows());
    $this->assertEmptyRow(FALSE);

    $this->setSearchValue('library_manager');
    $this->assertEquals(3, $this->getCountRows());
    $this->assertEmptyRow(FALSE);

    // Let's do search by library name only.
    $this->setSearchValue('alpha');
    $this->assertEquals(1, $this->getCountRows());

    $this->setSearchValue('non existing library');
    $this->assertEquals(0, $this->getCountRows());
    $this->assertEmptyRow(TRUE);

    // Reset the search string and check that we are back to the initial stage.
    $this->setSearchValue('');
    $this->assertGreaterThan($minimal_library_count, $this->getCountRows());
    $this->assertEmptyRow(FALSE);
  }

  /**
   * Sets search value.
   */
  protected function setSearchValue($value) {
    $page = $this->getSession()->getPage();
    $input = $page->find('css', '[data-drupal-selector = "library-filter"]');
    $input->setValue($value);
    if ($value == '') {
      // Some trick to fire keyUp event.
      // See Drupal\Tests\views_ui\FunctionalJavascript\ViewsListingTest.
      $input->keyUp(1);
    }
    // Wait until Drupal.debounce() has fired the callback.
    sleep(1);
  }

  /**
   * Returns total number of visible rows.
   */
  protected function getCountRows() {
    $page = $this->getSession()->getPage();
    $rows = $page->findAll('css', '[data-drupal-selector = "library-list"] tbody tr:not(.empty-row)');
    $rows = array_filter($rows, function ($row) {
      /** @var \Behat\Mink\Element\NodeElement $row */
      return $row->isVisible();
    });
    return count($rows);
  }

  /**
   * Passes if empty row has a valid visibility.
   */
  protected function assertEmptyRow($visible) {
    $page = $this->getSession()->getPage();
    /** @var \Behat\Mink\Element\NodeElement $empty_row */
    $empty_row = $page->findAll('css', '.empty-row')[0];
    $visible ?
      $this->assertTrue($empty_row->isVisible()) :
      $this->assertFalse($empty_row->isVisible());
  }

}
