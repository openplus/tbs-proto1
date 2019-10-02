<?php

namespace Drupal\Tests\library_manager\Functional;

use TestBase\BrowserTestBase;

/**
 * Base class for Library manager web tests.
 */
abstract class TestBase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['library_manager', 'library_manager_test'];

  /**
   * {@inheritdoc}
   *
   * @see https://www.drupal.org/node/2787529
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $user = $this->drupalCreateUser(['administer libraries', 'access site reports']);
    $this->drupalLogin($user);
  }

  /**
   * Passes if a given table header exists.
   */
  protected function assertTableHeader($xpath, array $expected_header) {
    $ths = $this->xpath($xpath);
    $this->assertEquals(count($expected_header), count($ths));
    foreach ($this->xpath($xpath) as $key => $th) {
      $this->assertEquals($expected_header[$key], $th->getHtml());
    }
  }

  /**
   * Clicks link located in given table row.
   *
   * @param string $row_text
   *   Text to identify a row.
   * @param string $link_text
   *   Text between the anchor tags.
   */
  protected function clickLinkInRow($row_text, $link_text) {
    $link_xpath = sprintf('//td[contains(., "%s")]/../td//a[text() = "%s"]', $row_text, $link_text);
    $link = $this->xpath($link_xpath)[0];
    $this->drupalGet($this->getAbsoluteUrl($link->getAttribute('href')));
  }

}
