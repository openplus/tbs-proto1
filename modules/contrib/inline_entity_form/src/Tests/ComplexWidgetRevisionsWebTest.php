<?php

namespace Drupal\inline_entity_form\Tests;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Extension\MissingDependencyException;

/**
 * IEF complex entity reference revisions tests.
 *
 * @group inline_entity_form
 */
class ComplexWidgetRevisionsWebTest extends InlineEntityFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'field_ui',
    'entity_reference_revisions',
    'inline_entity_form_test',
  ];

  /**
   * URL to add new content.
   *
   * @var string
   */
  protected $formContentAddUrl;

  /**
   * Prepares environment for
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->createUser([
      'create err_level_1 content',
      'edit any err_level_1 content',
      'delete any err_level_1 content',
      'create err_level_2 content',
      'edit any err_level_2 content',
      'delete any err_level_2 content',
      'create err_level_3 content',
      'edit any err_level_3 content',
      'delete any err_level_3 content',
      'view own unpublished content',
      'administer content types',
    ]);
    $this->drupalLogin($this->user);

    $this->formContentAddUrl = 'node/add/err_level_1';
  }

  /**
   * Tests saving entity reference revisions' field types at depth.
   */
  public function testRevisionsAtDepth() {
    $this->drupalGet($this->formContentAddUrl);

    // Open up level 2 and 3 IEF forms.
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add new node" and @data-drupal-selector="edit-field-level-2-items-actions-ief-add"]'));
    $this->assertResponse(200, 'Opening level 2 items inline form was successful.');
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Add new node" and @data-drupal-selector="edit-field-level-2-items-form-inline-entity-form-field-level-3-items-actions-ief-add"]'));
    $this->assertResponse(200, 'Opening level 3 items inline form was successful.');

    // Save level 3 IEF form.
    $edit = ['field_level_2_items[form][inline_entity_form][field_level_3_items][form][inline_entity_form][title][0][value]' => 'Level 3'];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @value="Create node" and @data-drupal-selector="edit-field-level-2-items-form-inline-entity-form-field-level-3-items-form-inline-entity-form-actions-ief-add-save"]'));
    $this->assertResponse(200, 'Creating level 3 node via inline form was successful.');

    // Save level 2 IEF form.
    $edit = ['field_level_2_items[form][inline_entity_form][title][0][value]' => 'Level 2'];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @value="Create node" and @data-drupal-selector="edit-field-level-2-items-form-inline-entity-form-actions-ief-add-save"]'));
    $this->assertResponse(200, 'Creating level 2 node via inline form was successful.');

    // Save the top level entity.
    $edit = ['title[0][value]' => 'Level 1'];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertResponse(200, 'Saving parent entity was successful.');

    // Re-edit the created node to test for revisions
    $node = $this->drupalGetNodeByTitle('Level 1');
    $this->drupalGet('node/' . $node->id() . '/edit');

    // Open up level 2 and 3 IEF forms.
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Edit" and @data-drupal-selector="edit-field-level-2-items-entities-0-actions-ief-entity-edit"]'));
    $this->assertResponse(200, 'Opening level 2 items inline form was successful.');
    $this->drupalPostAjaxForm(NULL, [], $this->getButtonName('//input[@type="submit" and @value="Edit" and @data-drupal-selector="edit-field-level-2-items-form-inline-entity-form-entities-0-form-field-level-3-items-entities-0-actions-ief-entity-edit"]'));
    $this->assertResponse(200, 'Opening level 3 items inline form was successful.');

    // Save level 3 IEF form.
    $edit = ['field_level_2_items[form][inline_entity_form][entities][0][form][field_level_3_items][form][inline_entity_form][entities][0][form][title][0][value]' => 'Level 3.1'];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @value="Update node" and @data-drupal-selector="edit-field-level-2-items-form-inline-entity-form-entities-0-form-field-level-3-items-form-inline-entity-form-entities-0-form-actions-ief-edit-save"]'));
    $this->assertResponse(200, 'Editing level 3 node via inline form was successful.');

    // Save level 2 IEF form.
    $edit = ['field_level_2_items[form][inline_entity_form][entities][0][form][title][0][value]' => 'Level 2.1'];
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName('//input[@type="submit" and @value="Update node" and @data-drupal-selector="edit-field-level-2-items-form-inline-entity-form-entities-0-form-actions-ief-edit-save"]'));
    $this->assertResponse(200, 'Editing level 2 node via inline form was successful.');

    // Save the top level entity.
    $edit = ['title[0][value]' => 'Level 1.1'];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertResponse(200, 'Saving parent entity was successful.');

    // $this->drupalGet('node/' . $node->id());
    // $this->assertText('Level 1.1', 'Top level node revision was saved and is active.');
    // $this->assertText('Level 2.1', 'Second level node revisions was saved and is active');
    // Issue #2721349: Nested inline entities should be saved in "inside-out" order
    // $this->assertText('Level 3.1', 'Top level node revision was saved and is active.');
  }
}
