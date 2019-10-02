<?php

namespace Drupal\Tests\lightning_workflow\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;

/**
 * @group lightning_workflow
 */
class ModerationSidebarTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_page',
    'lightning_roles',
    'lightning_workflow',
    'moderation_sidebar',
    'toolbar',
  ];

  /**
   * Tests that the given role can use moderation sidebar.
   *
   * @param string $role
   *   The role ID to test.
   *
   * @dataProvider provider
   */
  public function test($role) {
    $role = Role::load($role);
    $this->assertInstanceOf(Role::class, $role);
    $this->assertTrue($role->hasPermission('access toolbar'));
    $this->assertTrue($role->hasPermission('use moderation sidebar'));

    $user = $this->createUser();
    $user->addRole($role->id());
    $user->save();
    $this->drupalLogin($user);

    $node = $this->createNode([
      'title' => 'Foo Bar',
      'type' => 'page',
    ]);
    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Tasks');
  }

  /**
   * Data provider for ::test().
   *
   * @return array
   *   The test data.
   */
  public function provider() {
    return [
      ['page_creator'],
      ['page_reviewer'],
    ];
  }

}
