<?php

namespace Drupal\Tests\lightning_scheduler\Kernel\Update;

use Drupal\KernelTests\KernelTestBase;

/**
 * @group lightning_scheduler
 */
class Update8003Test extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_moderation',
    'lightning_scheduler',
    'system',
    'user',
  ];

  /**
   * Tests that the config object is created.
   */
  public function testUpdate() {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');

    // Assert the config object does not already exist.
    $is_new = $config_factory
      ->getEditable('lightning_scheduler.settings')
      ->isNew();
    $this->assertTrue($is_new);

    // Run the update.
    module_load_install('lightning_scheduler');
    lightning_scheduler_update_8003();

    // Assert the config object was created.
    $time_step = $config_factory
      ->get('lightning_scheduler.settings')
      ->get('time_step');
    $this->assertSame(60, $time_step);
  }

}
