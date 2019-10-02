<?php

namespace Drupal\Tests\lightning_workflow\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_workflow\Update\Update330;
use Drupal\views\Entity\View;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @group lightning
 * @group lightning_workflow
 *
 * @coversDefaultClass \Drupal\lightning_workflow\Update\Update330
 */
class Update330Test extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * @covers ::fixModerationHistory
   */
  public function testFixModerationHistory() {
    $this->container->get('module_installer')->install([
      'lightning_roles',
      'views',
    ]);
    $view = View::create([
      'id' => 'moderation_history',
      'base_table' => 'node_field_revision',
      'display' => [
        'default' => [
          'display_plugin' => 'default',
          'id' => 'default',
          'display_options' => [
            'fields' => [
              'uid' => [
                'id' => 'uid',
                'table' => 'node_field_revision',
                'field' => 'uid',
                'admin_label' => 'Authored by',
                'entity_field' => 'uid',
              ],
              'created' => [
                'id' => 'created',
                'table' => 'node_field_revision',
                'field' => 'created',
                'admin_label' => 'Authored on',
                'entity_field' => 'created',
              ],
              'moderation_state' => [
                'id' => 'moderation_state',
                'table' => 'content_moderation_state_field_revision',
                'field' => 'moderation_state',
                'alter' => [
                  'text' => 'Set to <strong>{{ moderation_state }}</strong> on {{ created }} by {{ uid }}',
                ],
              ],
            ],
            'relationships' => [],
          ],
        ],
      ],
    ]);
    $view->save();

    // Run the update.
    $message = 'Do you want to fix the Moderation History view to prevent incorrect timestamps and authors from being displayed?';
    $io = $this->prophesize(StyleInterface::class);
    $io->confirm($message)->shouldBeCalled()->willReturn(TRUE);
    $this->container->get('class_resolver')
      ->getInstanceFromDefinition(Update330::class)
      ->fixModerationHistory($io->reveal());

    // Assert the view has changed.
    $display = View::load('moderation_history')->getDisplay('default');
    $this->assertInternalType('array', $display['display_options']['fields']['revision_uid']);
    $this->assertInternalType('array', $display['display_options']['fields']['revision_timestamp']);
    $this->assertSame('Set to <strong>{{ moderation_state }}</strong> on {{ revision_timestamp }} by {{ revision_uid }}', $display['display_options']['fields']['moderation_state']['alter']['text']);
    $this->assertArrayNotHasKey('uid', $display['display_options']['fields']);
    $this->assertArrayNotHasKey('created', $display['display_options']['fields']);
    $this->assertInternalType('array', $display['display_options']['relationships']['revision_uid']);
  }

}
