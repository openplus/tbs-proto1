<?php

namespace Drupal\Tests\lightning_layout;

use Drupal\block\Entity\Block;
use Drupal\Tests\lightning_core\FixtureBase;
use Drupal\user\Entity\Role;

final class FixtureContext extends FixtureBase {

  /**
   * @BeforeScenario
   */
  public function setUp() {
    // Create the administrator role if it does not already exist.
    if (! Role::load('administrator')) {
      $role = Role::create([
        'id' => 'administrator',
        'label' => 'Administrator'
      ])->setIsAdmin(TRUE);

      $this->save($role);
    }

    // Place the main content block if it's not already there.
    if (! Block::load('bartik_content')) {
      $block = Block::create([
        'id' => 'bartik_content',
        'theme' => 'bartik',
        'region' => 'content',
        'plugin' => 'system_main_block',
        'settings' => [
          'label_display' => '0',
        ],
      ]);
      $this->save($block);
    }
    // Place the local tasks block if it's not already there.
    if (! Block::load('bartik_local_tasks')) {
      $block = Block::create([
        'id' => 'bartik_local_tasks',
        'theme' => 'bartik',
        'region' => 'content',
        'plugin' => 'local_tasks_block',
        'settings' => [
          'label_display' => '0',
        ],
      ]);
      $this->save($block);
    }

    $this->installModule('lightning_roles');
    $this->installModule('pathauto');
    $this->installModule('views');

    // Grant permissions needed for testing.
    Role::load('landing_page_creator')
      ->grantPermission('access user profiles')
      ->grantPermission('set panelizer default')
      ->grantPermission('use editorial transition create_new_draft')
      ->grantPermission('use editorial transition publish')
      ->save();

    // Use Bartik for testing.
    $this->installTheme('seven');
    $this->installTheme('bartik');

    $this->config('system.theme')
      ->set('admin', 'seven')
      ->set('default', 'bartik')
      ->save();
  }

  /**
   * @AfterScenario
   */
  public function tearDown() {
    // Revoke permissions used for testing.
    Role::load('landing_page_creator')
      ->revokePermission('access user profiles')
      ->revokePermission('set panelizer default')
      ->revokePermission('use editorial transition create_new_draft')
      ->revokePermission('use editorial transition publish')
      ->save();

    parent::tearDown();
  }

}
