<?php

namespace Drupal\Tests\lightning_workflow\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @group lightning_workflow
 */
class VersioningTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'lightning_page',
    'lightning_workflow',
  ];

  /**
   * Tests that the edit form loads the latest revision.
   */
  public function testLatestRevisionIsLoadedByEditForm() {
    $this->drupalPlaceBlock('local_tasks_block');

    $account = $this->createUser([
      'create page content',
      'edit own page content',
      'view latest version',
      'view own unpublished content',
      'use editorial transition create_new_draft',
      'use editorial transition publish',
    ]);
    $this->drupalLogin($account);
    $this->drupalGet('/node/add/page');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('Title')->setValue('Smells Like Teen Spirit');
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('Published');
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->fieldExists('Title')->setValue('Polly');
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('Draft');
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->fieldValueEquals('Title', 'Polly');
  }

}
