<?php

namespace Drupal\Tests\library_manager\Functional;

/**
 * Tests the Library definition interface.
 *
 * @group library_manager
 */
class LibraryDefinitionTest extends TestBase {

  /**
   * Test callback.
   */
  public function testDefinitionCollectionPage() {
    $this->drupalGet('admin/structure/library/definition');

    $this->assertPageTitle('Library definitions');

    $expected_header = [
      'Machine name',
      'Version',
      'License',
      'Operations',
    ];
    $this->assertTableHeader('//main//table//th', $expected_header);

    $alpha_row = $this->xpath('//td[position() = 1 and text() = "alpha"]/../td');
    $this->assertEquals('1.2.3', $alpha_row[1]->getText());

    $this->assertEquals('GNU-GPL-2.0-or-later', $alpha_row[2]->getText());

    /* @var \Behat\Mink\Element\NodeElement[] $operations */
    $operations = $alpha_row[3]->findAll('xpath', './/a');

    $this->assertEquals('Edit', $operations[0]->getText());
    $this->assertEquals('Duplicate', $operations[1]->getText());
    $this->assertEquals('Delete', $operations[2]->getText());
  }

  /**
   * Test callback.
   */
  public function testDefinitionForm() {
    $this->drupalGet('admin/structure/library/definition/add');
    $this->assertPageTitle('Add library definition');

    // Check form labels.
    $this->assertXpath('//div[contains(@class, "form-item-id")]/label[text() = "Machine name"]');
    $this->assertXpath('//div[contains(@class, "form-item-mode")]/label[text() = "Register new library"]');
    $this->assertXpath('//div[contains(@class, "form-item-mode")]/label[text() = "Override existing library"]');
    $this->assertXpath('//div[contains(@class, "form-item-target")]/label[text() = "Library to override"]');
    $this->assertXpath('//div[contains(@class, "form-item-remote")]/label[text() = "Remote"]');
    $this->assertXpath('//div[contains(@class, "form-item-version")]/label[text() = "Version"]');
    $this->assertXpath('//div[contains(@class, "form-item-license-name")]/label[text() = "Name"]');
    $this->assertXpath('//div[contains(@class, "form-item-license-url")]/label[text() = "URL"]');
    $this->assertXpath('//div[contains(@class, "form-item-license-gpl-compatible")]/label[text() = "GPL compatible"]');

    $header = ['Name', 'Size', 'Type', 'Operations'];

    $this->assertXpath('//fieldset[@id = "edit-js"]/legend/span[text() = "JS files"]');
    $this->assertTableHeader('//fieldset[@id = "edit-js"]//th', $header);
    $this->assertXpath('//fieldset[@id = "edit-js"]//table//td[text() = "JS files are not configured yet."]');

    $this->assertXpath('//fieldset[@id = "edit-css"]/legend/span[text() = "CSS files"]');
    $this->assertTableHeader('//fieldset[@id = "edit-css"]//th', $header);
    $this->assertXpath('//fieldset[@id = "edit-css"]//table//td[text() = "CSS files are not configured yet."]');

    $this->assertXpath('//fieldset[@id = "edit-dependencies"]/legend/span[text() = "Dependencies"]');
    $this->assertXpath('//fieldset[@id = "edit-dependencies"]//input[@type = "submit" and @value = "Add dependency"]');

    $this->assertXpath('//div[contains(@class, "form-item-load")]/label[text() = "Load the library automatically according to visibility rules"]');

    $tabs_prefix = '//div[contains(@class, "form-type-vertical-tabs")]';
    $this->assertXpath($tabs_prefix . '/label[text() = "Visibility"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-current-theme"]/summary[text() = "Current Theme"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-current-theme"]/div//label[text() = "Theme"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-current-theme"]/div//label[text() = "Negate the condition"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-request-path"]/summary[text() = "Request Path"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-request-path"]/div//label[text() = "Pages"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-request-path"]/div//label[text() = "Negate the condition"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-user-role"]/summary[text() = "User Role"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-user-role"]/div//fieldset/legend/span[text() = "When the user has the following roles"]');
    $this->assertXpath($tabs_prefix . '//details[@id = "edit-visibility-user-role"]/div//label[text() = "Negate the condition"]');

    $edit = [
      'id' => 'foo',
      'mode' => 'override',
      'target' => 'core/backbone',
      'remote' => 'https://www.drupal.org/foo',
      'version' => '3.4.5',
      'license[name]' => 'MIT',
      'license[url]' => 'http://example.com/MIT-LICENSE.txt',
      'license[gpl-compatible]' => TRUE,
      'library_dependencies[0]' => 'core/jquery',
      'load' => TRUE,
      'visibility[current_theme][theme]' => 'classy',
      'visibility[current_theme][negate]' => TRUE,
      'visibility[request_path][pages]' => '/user',
      'visibility[user_role][roles][anonymous]' => TRUE,
    ];

    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertStatusMessage('Library definition has been created.');

    // Check default form values for newly created definition.
    $this->drupalGet('admin/structure/library/definition/foo/edit');

    $this->assertXpath('//input[@name = "id" and @value = "foo"]');
    $this->assertXpath('//input[@name = "mode" and @value = "override"]');
    $this->assertXpath('//input[@name = "target" and @value = "core/backbone"]');
    $this->assertXpath('//input[@name = "remote" and @value = "https://www.drupal.org/foo"]');
    $this->assertXpath('//input[@name = "version" and @value = "3.4.5"]');
    $this->assertXpath('//input[@name = "license[name]" and @value = "MIT"]');
    $this->assertXpath('//input[@name = "license[url]" and @value = "http://example.com/MIT-LICENSE.txt"]');
    $this->assertXpath('//input[@name = "license[gpl-compatible]" and @checked]');
    $this->assertXpath('//input[@name = "library_dependencies[0]" and @value = "core/jquery"]');
    $this->assertXpath('//input[@name = "load" and @checked = "checked"]');
    $this->assertXpath('//select[@name = "visibility[current_theme][theme]"]/option[@value = "classy" and @selected = "selected"]');
    $this->assertXpath('//input[@name = "visibility[current_theme][negate]" and @checked = "checked"]');
    $this->assertXpath('//textarea[@name = "visibility[request_path][pages]" and text() = "/user"]');
    $this->assertXpath('//input[@name = "visibility[request_path][negate]" and not(@checked)]');
    $this->assertXpath('//input[@name = "visibility[user_role][roles][anonymous]" and @checked = "checked"]');
    $this->assertXpath('//input[@name = "visibility[user_role][negate]" and not(@checked)]');

    // Check delete form.
    $delete_url = $this->getAbsoluteUrl($this->xpath('//a[@id = "edit-delete"]')[0]->getAttribute('href'));
    $this->drupalGet($delete_url);
    $this->assertPageTitle(t('Are you sure you want to delete the library definition %definition?', ['%definition' => 'foo']));
    $this->assertXpath('//form[contains(text(), "This action cannot be undone.")]');
    $this->clickLink('Cancel');
    $this->assertPageTitle('Library definitions');
    $this->drupalGet($delete_url);
    $this->drupalPostForm(NULL, [], 'Delete');
    $this->assertStatusMessage(t('Deleted library definition %definition_name.', ['%definition_name' => 'foo']));
    $this->assertPageTitle('Library definitions');
  }

  /**
   * Test callback.
   */
  public function testJsFileForm() {
    $this->drupalGet('admin/structure/library/definition/alpha/edit');
    $this->clickLink('Add JS file');

    $this->assertPageTitle('Add JS file');

    // Check form labels.
    $this->assertXpath('//div[contains(@class, "form-item-file-name")]/label[text() = "File name"]');
    $this->assertXpath('//div[contains(@class, "form-item-preprocess")]/label[text() = "Preprocess"]');
    $this->assertXpath('//div[contains(@class, "form-item-minified")]/label[text() = "Minified"]');
    $this->assertXpath('//div[contains(@class, "form-item-weight")]/label[text() = "Weight"]');
    $this->assertXpath('//div[contains(@class, "form-item-external")]/label[text() = "External"]');
    $this->assertXpath('//div[contains(@class, "form-item-code")]/label[text() = "Code"]');

    // Check file name validation.
    $edit = [
      'file_name' => '/foo.js',
      'preprocess' => FALSE,
      'minified' => TRUE,
      'weight' => -5,
      'external' => FALSE,
      'code' => 'console.log(123);',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertErrorMessage('The file name is not correct.');

    $edit['file_name'] = 'foo.php';
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertErrorMessage('The file name is not correct.');

    $edit['file_name'] = 'bar/../foo.js';
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertErrorMessage('The file name is not correct.');

    $edit['file_name'] = 'foo.js';
    $this->drupalPostForm(NULL, $edit, 'Save');

    $this->assertStatusMessage('The JS file has been saved.');
    $this->assertPageTitle('Edit library definition');

    $this->clickLinkInRow('foo.js', 'Edit');
    $this->assertPageTitle('Edit JS file');

    // Check default form values for newly created JS file.
    $this->assertXpath('//input[@name = "file_name" and @value = "foo.js"]');
    $this->assertXpath('//input[@name = "preprocess" and not(@checked)]');
    $this->assertXpath('//input[@name = "minified" and @checked = "checked"]');
    $this->assertXpath('//select[@name = "weight"]/option[@value = "-5" and @selected = "selected"]');
    $this->assertXpath('//input[@name = "external" and not(@checked)]');
    $this->assertXpath('//textarea[@name = "code" and text() = "console.log(123);"]');

    $this->clickLink('Delete');

    $this->assertPageTitle('Are you sure you want to delete the file?');
    $this->assertXpath('//form[contains(text(), "This action cannot be undone.")]');
    $this->clickLink('Cancel');
    $this->assertPageTitle('Edit library definition');

    $this->clickLinkInRow('foo.js', 'Delete');
    $this->drupalPostForm(NULL, [], 'Delete');

    $this->assertStatusMessage('The JS file has been deleted.');
    $this->assertPageTitle('Edit library definition');
  }

  /**
   * Test callback.
   */
  public function testCssFileForm() {
    $this->drupalGet('admin/structure/library/definition/alpha/edit');
    $this->clickLink('Add CSS file');

    $this->assertPageTitle('Add CSS file');

    // Check form labels.
    $this->assertXpath('//div[contains(@class, "form-item-file-name")]/label[text() = "File name"]');
    $this->assertXpath('//div[contains(@class, "form-item-preprocess")]/label[text() = "Preprocess"]');
    $this->assertXpath('//div[contains(@class, "form-item-minified")]/label[text() = "Minified"]');
    $this->assertXpath('//div[contains(@class, "form-item-external")]/label[text() = "External"]');
    $this->assertXpath('//div[contains(@class, "form-item-url")]/label[text() = "Url"]');
    $this->assertXpath('//div[contains(@class, "form-item-code")]/label[text() = "Code"]');

    // Check file name validation.
    $edit = [
      'file_name' => '/foo.css',
      'preprocess' => FALSE,
      'minified' => TRUE,
      'weight' => 5,
      'external' => FALSE,
      'url' => 'http://example.com/foo.css',
    ];

    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertErrorMessage('The file name is not correct.');

    $edit['file_name'] = 'foo.php';
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertErrorMessage('The file name is not correct.');

    $edit['file_name'] = 'bar/../foo.css';
    $this->drupalPostForm(NULL, $edit, 'Save');
    $this->assertErrorMessage('The file name is not correct.');

    $edit['file_name'] = 'foo.css';
    $this->drupalPostForm(NULL, $edit, 'Save');

    $this->assertStatusMessage('The CSS file has been saved.');
    $this->assertPageTitle('Edit library definition');

    $this->clickLinkInRow('foo.css', 'Edit');
    $this->assertPageTitle('Edit CSS file');

    // Check default form values for newly created JS file.
    $this->assertXpath('//input[@name = "file_name" and @value = "foo.css"]');
    $this->assertXpath('//input[@name = "preprocess" and not(@checked)]');
    $this->assertXpath('//input[@name = "minified" and @checked = "checked"]');
    $this->assertXpath('//select[@name = "weight"]/option[@value = 5 and @selected = "selected"]');
    $this->assertXpath('//input[@name = "external" and not(@checked)]');
    $this->assertXpath('//input[@name = "url" and @value = "http://example.com/foo.css"]');

    $this->clickLink('Delete');

    $this->assertPageTitle('Are you sure you want to delete the file?');
    $this->assertXpath('//form[contains(text(), "This action cannot be undone.")]');
    $this->clickLink('Cancel');
    $this->assertPageTitle('Edit library definition');

    $this->clickLinkInRow('foo.css', 'Delete');

    $this->drupalPostForm(NULL, [], 'Delete');

    $this->assertStatusMessage('The CSS file has been deleted.');
    $this->assertPageTitle('Edit library definition');
  }

  /**
   * Test callback.
   */
  public function testLibraryDefinitionDuplicate() {
    $this->drupalGet('admin/structure/library/definition');
    $this->clickLinkInRow('alpha', 'Duplicate');
    $this->assertPageTitle('Duplicate of alpha');
    $this->assertXpath('//input[@name = "id" and @value = "duplicate_of_alpha"]');
    $this->drupalPostForm(NULL, ['id' => 'alpha'], 'Duplicate');
    $this->assertErrorMessage('The machine-readable name is already in use. It must be unique.');
    $this->drupalPostForm(NULL, ['id' => 'beta'], 'Duplicate');
    $this->assertPageTitle('Edit library definition');
    $this->assertXpath('//input[@name = "id" and @value = "beta"]');
    $this->assertXpath('//input[@name = "version" and @value = "1.2.3"]');
    $this->assertXpath('//a[contains(@href, "/sites/default/files/libraries/custom/beta/example.js") and text() = "example.js"]');
  }

}
