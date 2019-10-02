<?php

namespace Drupal\Tests\codemirror_editor\FunctionalJavascript;

/**
 * Tests the CodeMirror text editor.
 *
 * @group codemirror_editor
 */
class TextEditorTest extends TestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->createContentType(['type' => 'page']);
    $this->createNode([
      'title' => 'Example',
      'body' => [
        'value' => 'Test',
        'format' => 'codemirror',
      ],
    ]);
  }

  /**
   * Test callback.
   */
  public function testTextEditor() {

    $permissions = [
      'administer filters',
      'edit any page content',
      'use text format codemirror',
      'use text format basic',
    ];
    $user = $this->drupalCreateUser($permissions);

    $this->drupalLogin($user);

    $this->drupalGet('node/1/edit');

    $this->editorSetValue('Test');
    $this->editorSetSelection([0, 0], [0, 4]);
    $this->editorClickButton('bold');
    $this->assertEditorValue('<strong>Test</strong>');

    $this->assertToolbarExists();
    $this->assertEditorOption('mode', 'text/html');
    $this->assertEditorOption('lineNumbers', FALSE);
    $this->assertEditorOption('foldGutter', FALSE);
    $this->assertEditorOption('autoCloseTags', TRUE);
    $this->assertEditorOption('styleActiveLine', FALSE);

    // Test if the editor is correctly attached and detached.
    $this->assertElementExist('//div[contains(@class, "CodeMirror")]');
    $this->setBodyFormat('basic');
    $this->assertElementNotExist('//div[contains(@class, "CodeMirror")]');
    $this->setBodyFormat('codemirror');
    $this->assertElementExist('//div[contains(@class, "CodeMirror")]');

    $this->drupalGet('admin/config/content/formats/manage/codemirror');

    // Make sure that the form displays default values.
    $this->assertElementExist('//select[@name = "editor[settings][mode]"]/optgroup/option[@value = "text/html" and @selected]');
    $this->assertElementExist('//input[@name = "editor[settings][toolbar]" and @checked]');
    $this->assertElementExist('//input[@name = "editor[settings][lineNumbers]" and not(@checked)]');
    $this->assertElementExist('//input[@name = "editor[settings][foldGutter]" and not(@checked)]');
    $this->assertElementExist('//input[@name = "editor[settings][autoCloseTags]" and @checked]');
    $this->assertElementExist('//input[@name = "editor[settings][styleActiveLine]" and not(@checked)]');

    $edit = [
      'editor[settings][mode]' => 'application/xml',
      'editor[settings][toolbar]' => FALSE,
      'editor[settings][lineNumbers]' => TRUE,
      'editor[settings][foldGutter]' => TRUE,
      'editor[settings][autoCloseTags]' => FALSE,
      'editor[settings][styleActiveLine]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');

    $this->drupalGet('node/1/edit');

    $this->assertToolbarNotExists();
    $this->assertEditorOption('mode', 'application/xml');
    $this->assertEditorOption('lineNumbers', TRUE);
    $this->assertEditorOption('foldGutter', TRUE);
    $this->assertEditorOption('autoCloseTags', FALSE);
    $this->assertEditorOption('styleActiveLine', TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function getWrapperSelector() {
    return '.js-form-item-body-0-value';
  }

  /**
   * Sets text format for body field.
   *
   * @param string $format
   *   The format.
   */
  protected function setBodyFormat($format) {
    $this->getSession()
      ->getPage()
      ->find('xpath', '//select[@name = "body[0][format]"]')
      ->selectOption($format);
    sleep(1);
  }

}
