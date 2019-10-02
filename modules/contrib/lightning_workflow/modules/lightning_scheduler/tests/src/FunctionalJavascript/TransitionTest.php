<?php

namespace Drupal\Tests\lightning_scheduler\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\lightning_scheduler\Traits\SchedulerUiTrait;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * @group lightning
 * @group lightning_workflow
 * @group lightning_scheduler
 */
class TransitionTest extends WebDriverTestBase {

  use CronRunTrait;
  use SchedulerUiTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'lightning_page',
    'lightning_scheduler',
    'lightning_workflow',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');

    $account = $this->createUser([
      'create page content',
      'view own unpublished content',
      'edit own page content',
      'use editorial transition create_new_draft',
      'use editorial transition review',
      'use editorial transition publish',
      'use editorial transition archive',
      'schedule editorial transition publish',
      'schedule editorial transition archive',
      'view latest version',
      'administer nodes',
    ]);
    $this->drupalLogin($account);
    $this->setUpTimeZone();
    $this->setTimeStep();
  }

  public function testPublishInPast() {
    $this->drupalGet('/node/add/page');
    $this->assertSession()->fieldExists('Title')->setValue($this->randomString());
    $this->createTransition('Published', time() - 10);
    $this->assertSession()->buttonExists('Save')->press();
    $this->cronRun();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->pageTextContains('Current state Published');
    $this->assertSession()->elementNotExists('css', '.scheduled-transition');
  }

  /**
   * @depends testPublishInPast
   */
  public function testSkipInvalidTransition() {
    $this->drupalGet('/node/add/page');
    $this->assertSession()->fieldExists('Title')->setValue($this->randomString());
    $this->createTransition('Published', time() - 20);
    $this->createTransition('Archived', time() - 10);
    $this->assertSession()->buttonExists('Save')->press();
    $this->cronRun();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    // It will still be in the draft state because the transition should resolve
    // to Draft -> Archived, which doesn't exist.
    $this->assertSession()->pageTextContains('Current state Draft');
    $this->assertSession()->elementNotExists('css', '.scheduled-transition');
  }

  public function testClearCompletedTransitions() {
    $this->drupalGet('/node/add/page');
    $this->assertSession()->fieldExists('Title')->setValue($this->randomString());
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('In review');
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->createTransition('Published', time() + 8);
    $this->assertSession()->buttonExists('Save')->press();
    sleep(10);
    $this->cronRun();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('Archived');
    $this->assertSession()->buttonExists('Save')->press();
    $this->cronRun();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->pageTextContains('Current state Archived');
  }

  public function testPublishPendingRevision() {
    $this->container->get('module_installer')->install(['views']);

    $this->drupalGet('/node/add/page');
    $this->assertSession()->fieldExists('Title')->setValue($this->randomString());
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('Published');
    $this->assertSession()->elementExists('named', ['link', 'Promotion options'])->click();
    $this->assertSession()->fieldExists('Promoted to front page')->check();
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->elementExists('css', 'a[rel="edit-form"]')->click();
    $this->assertSession()->fieldExists('Title')->setValue('MC Hammer');
    $this->assertSession()->selectExists('moderation_state[0][state]')->selectOption('Draft');
    $this->createTransition('Published', time() + 8);
    $this->assertSession()->buttonExists('Save')->press();
    sleep(10);
    $this->cronRun();
    $this->drupalGet('/node');
    $this->assertSession()->linkExists('MC Hammer');
  }

}
