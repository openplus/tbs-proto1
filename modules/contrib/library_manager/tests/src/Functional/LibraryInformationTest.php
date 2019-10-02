<?php

namespace Drupal\Tests\library_manager\Functional;

use Drupal\Core\Url;

/**
 * Tests the Library information interface.
 *
 * @group library_manager
 */
class LibraryInformationTest extends TestBase {

  /**
   * Test callback.
   */
  public function testCollectionPage() {
    $this->drupalGet('admin/structure/library');

    $this->assertPageTitle('Libraries');

    $this->assertXpath('//input[@data-drupal-selector = "library-filter" and @placeholder = "Library name"]');

    $expected_header = [
      'Name',
      'Version',
      'License',
      'Definition',
      'Operations',
    ];
    $this->assertTableHeader('//main//table//th', $expected_header);

    // Check some arbitrary libraries in the list.
    $this->assertXpath('//tr[td[1]/a[text() = "core/backbone"]][td[3][text() = "MIT"]]');
    $this->assertXpath('//tr[td[1]/a[text() = "system/drupal.system.modules"]][td[3][text() = "GNU-GPL-2.0-or-later"]]');
    $this->assertXpath('//tr[td[1]/a[text() = "library_manager/library_manager"]][td[3][text() = "GNU-GPL-2.0-or-later"]]');

    // Check definition link.
    $this->clickLinkInRow('library_manager/alpha', 'alpha');
    $this->assertSession()->addressEquals('admin/structure/library/definition/alpha/edit');
    $this->assertPageTitle('Edit library definition');
  }

  /**
   * Test callback.
   */
  public function testDetailsPage() {

    $module_path = drupal_get_path('module', 'library_manager_test');

    $this->drupalGet('admin/structure/library');
    $this->clickLinkInRow('library_manager_test/library_manager_test', 'library_manager_test/library_manager_test');
    $this->assertSession()->addressEquals('admin/structure/library/library/library_manager_test/library_manager_test');
    $this->assertPageTitle('library_manager_test');

    $labels = $this->xpath('//div[contains(@class, "form-type-item")]/label');

    $data = [];
    foreach ($labels as $label) {
      $data[$label->getText()] = $label->find('xpath', './following-sibling::text()')->getText();
    }

    $expected_data = [
      'Name' => 'library_manager_test',
      'Extension' => 'library_manager_test',
      'Version' => '2.3.4',
      'License' => 'GNU-GPL-2.0-or-later',
    ];

    $this->assertEquals($expected_data, $data);

    $fieldsets = $this->xpath('//fieldset[contains(@class, "form-wrapper")]');

    // JS files.
    $this->assertTrue($fieldsets[0]->find('xpath', './legend/span[text() = "JS"]'));
    /* @var \Behat\Mink\Element\NodeElement[] $js_items */
    $js_items = $fieldsets[0]->findAll('xpath', './div/ul/li');

    $file = $module_path . '/js/example.js';
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', Url::fromUri('base://' . $file)->toString(), $file);
    $this->assertTrue($js_items[0]->find('xpath', $xpath));

    $file = 'libraries/example/global.js';
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', Url::fromUri('base://' . $file)->toString(), $file);
    $this->assertTrue($js_items[1]->find('xpath', $xpath));

    $file = 'http://example.com/external.js';
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', $file, $file);
    $this->assertTrue($js_items[2]->find('xpath', $xpath));

    // CSS files.
    $this->assertTrue($fieldsets[1]->find('xpath', './legend/span[text() = "CSS"]'));
    /* @var \Behat\Mink\Element\NodeElement[] $css_items */
    $css_items = $fieldsets[1]->findAll('xpath', './div/ul/li');

    $file = $module_path . '/css/example.css';
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', Url::fromUri('base://' . $file)->toString(), $file);
    $this->assertTrue($css_items[0]->find('xpath', $xpath));

    $file = 'libraries/example/global.css';
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', Url::fromUri('base://' . $file)->toString(), $file);
    $this->assertTrue($css_items[1]->find('xpath', $xpath));

    $file = 'http://example.com/external.css';
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', $file, $file);
    $this->assertTrue($css_items[2]->find('xpath', $xpath));

    // Dependencies.
    $this->assertTrue($fieldsets[2]->find('xpath', './legend/span[text() = "Dependencies"]'));
    /* @var \Behat\Mink\Element\NodeElement[] $dependency_items */
    $dependency_items = $fieldsets[2]->findAll('xpath', './div/ul/li');

    $url = Url::fromUri('base://admin/structure/library/library/core/jquery');
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', $url->toString(), 'core/jquery');
    $this->assertTrue($dependency_items[0]->find('xpath', $xpath));

    $url = Url::fromUri('base://admin/structure/library/library/core/drupal');
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', $url->toString(), 'core/drupal');
    $this->assertTrue($dependency_items[1]->find('xpath', $xpath));

    $url = Url::fromUri('base://admin/structure/library/library/core/drupal.dialog.ajax');
    $xpath = sprintf('/a[@href = "%s" and text() = "%s"]', $url->toString(), 'core/drupal.dialog.ajax');
    $this->assertTrue($dependency_items[2]->find('xpath', $xpath));

    // Check if dependency link is valid.
    $this->clickLink('core/jquery');
    $this->assertPageTitle('jquery');

    // Check "Required by" section.
    $this->assertXpath('//fieldset/legend/span[text() = "Required by"]');
    $this->click('//fieldset/div/ul/li/a[text() = "library_manager_test/library_manager_test"]');
    $this->assertPageTitle('library_manager_test');
  }

  /**
   * Test callback.
   */
  public function testDefinitionLink() {
    $this->drupalGet('admin/structure/library');
    $this->clickLinkInRow('alpha', 'library_manager/alpha');
    $this->assertPageTitle('alpha');
    $this->clickLink('alpha');
    $this->assertPageTitle('Edit library definition');
  }

  /**
   * Test callback.
   */
  public function testExportPage() {
    $this->drupalGet('admin/structure/library');
    $this->clickLinkInRow('library_manager_test/library_manager_test', 'Export');
    $this->assertSession()->addressEquals('admin/structure/library/library/library_manager_test/library_manager_test/export');
    $this->assertPageTitle('library_manager_test');
    $this->assertXpath('//textarea[contains(@class, "library-export") and @data-codemirror and contains(text(), "version: 2.3.4")]');
  }

  /**
   * Test callback.
   */
  public function testAutocompletePage() {
    $this->drupalGet('admin/structure/library/autocomplete', ['query' => ['q' => 'backbone']]);
    $assert_session = $this->assertSession();
    $assert_session->statusCodeEquals(200);
    $assert_session->responseHeaderEquals('Content-Type', 'application/json');
    $data = json_decode($this->getSession()->getPage()->getContent());
    $this->assertEquals('core/backbone', $data[0]->label);
    $this->assertEquals('core/backbone', $data[0]->value);
  }

}
