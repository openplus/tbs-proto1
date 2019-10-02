<?php

namespace Drupal\Tests\lightning_workflow\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group lightning_workflow
 */
class ModerationControlTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_moderation',
    'lightning_workflow',
    'system',
    'workflows',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('lightning_workflow');
    $this->container->get('module_installer')->install(['lightning_page']);
  }

  /**
   * Tests that moderation controls are hidden if Moderation Sidebar is enabled.
   */
  public function testHiddenOnModerationSidebarInstall() {
    $display = EntityViewDisplay::load('node.page.default');
    $this->assertInstanceOf(EntityViewDisplay::class, $display);
    $this->assertArrayHasKey('content_moderation_control', $display->getComponents());

    $this->container->get('module_installer')->install(['moderation_sidebar']);

    $display = EntityViewDisplay::load('node.page.default');
    $this->assertInstanceOf(EntityViewDisplay::class, $display);
    $hidden = $display->get('hidden');
    $this->assertInternalType('array', $hidden);
    $this->assertTrue($hidden['content_moderation_control']);
  }

}
