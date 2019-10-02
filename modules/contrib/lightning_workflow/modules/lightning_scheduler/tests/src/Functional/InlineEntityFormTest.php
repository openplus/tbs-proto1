<?php

namespace Drupal\Tests\lightning_scheduler\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\lightning_scheduler\Traits\SchedulerUiTrait;
use Drupal\workflows\Entity\Workflow;

/**
 * @group lightning_workflow
 * @group lightning_scheduler
 *
 * @requires inline_entity_form
 */
class InlineEntityFormTest extends BrowserTestBase {

  use SchedulerUiTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'inline_entity_form',
    'lightning_scheduler',
    'lightning_workflow',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->createContentType(['type' => 'alpha']);
    $this->createContentType(['type' => 'beta']);

    $field_storage = entity_create('field_storage_config', [
      'type' => 'entity_reference',
      'entity_type' => 'user',
      'settings' => [
        'target_type' => 'node',
      ],
      'field_name' => 'field_inline_entity',
    ]);
    $this->assertSame(SAVED_NEW, $field_storage->save());

    entity_create('field_config', [
      'field_storage' => $field_storage,
      'bundle' => 'user',
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            'alpha' => 'alpha',
          ],
        ],
      ],
      'label' => 'Inline entity',
    ])->save();

    entity_get_form_display('user', 'user', 'default')
      ->setComponent('field_inline_entity', [
        'type' => 'inline_entity_form_simple',
      ])
      ->save();

    $field_storage = entity_create('field_storage_config', [
      'type' => 'entity_reference',
      'entity_type' => 'node',
      'settings' => [
        'target_type' => 'node',
      ],
      'field_name' => 'field_inline_entity',
    ]);
    $this->assertSame(SAVED_NEW, $field_storage->save());

    entity_create('field_config', [
      'field_storage' => $field_storage,
      'bundle' => 'alpha',
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            'beta' => 'beta',
          ],
        ],
      ],
      'label' => 'Inline entity',
    ])->save();

    entity_get_form_display('node', 'alpha', 'default')
      ->setComponent('field_inline_entity', [
        'type' => 'inline_entity_form_simple',
      ])
      ->save();

    /** @var \Drupal\workflows\Entity\Workflow $workflow */
    $workflow = Workflow::load('editorial');
    /** @var \Drupal\content_moderation\Plugin\WorkflowType\ContentModerationInterface $plugin */
    $plugin = $workflow->getTypePlugin();
    $plugin->addEntityTypeAndBundle('node', 'alpha');
    $plugin->addEntityTypeAndBundle('node', 'beta');
    $workflow->save();

    // Inline Entity Form has a problem referencing entities with other than
    // admin users.
    // @see https://www.drupal.org/project/inline_entity_form/issues/2753553
    $this->drupalLogin($this->rootUser);
  }

  /**
   * Asserts that an inline entity form for field_inline_entity exists.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The inline entity form element.
   */
  private function assertInlineEntityForm() {
    return $this->assertSession()
      ->elementExists('css', '#edit-field-inline-entity-wrapper');
  }

  public function testHostEntityWithoutModeration() {
    // Test with an un-moderated host entity.
    $this->drupalGet('/user/' . $this->rootUser->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);
    $inline_entity_form = $this->assertInlineEntityForm();
    $this->assertSession()->fieldExists('Title', $inline_entity_form)->setValue('Kaboom?');
    $this->assertSession()->selectExists('field_inline_entity[0][inline_entity_form][moderation_state][0][state]', $inline_entity_form);
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * @depends testHostEntityWithoutModeration
   */
  public function testHostEntityWithModeration() {
    // Test with a moderated host entity.
    $this->drupalGet('node/add/alpha');
    $this->assertSession()->fieldExists('Title')->setValue('Foobar');
    $inline_entity_form = $this->assertInlineEntityForm();
    $this->assertSession()->fieldExists('Title', $inline_entity_form)->setValue('Foobaz');

    $host_field = 'moderation_state[0][scheduled_transitions][data]';
    $inline_field = 'field_inline_entity[0][inline_entity_form][moderation_state][0][scheduled_transitions][data]';

    $transition_1 = [
      [
        'state' => 'published',
        'when' => time() + 100,
      ],
    ];
    $transition_2 = [
      [
        'state' => 'published',
        'when' => time() + 200,
      ],
    ];
    $this->setTransitionData($host_field, $transition_1);
    $this->setTransitionData($inline_field, $transition_2);
    $this->assertSession()->buttonExists('Save')->press();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('node');
    $alpha = $storage->loadByProperties(['type' => 'alpha']);
    $beta = $storage->loadByProperties(['type' => 'beta']);
    $this->assertCount(1, $alpha);
    $this->assertCount(1, $beta);

    $this->drupalGet(reset($alpha)->toUrl('edit-form'));
    $this->assertTransitionData($host_field, $transition_1);

    $this->drupalGet(reset($beta)->toUrl('edit-form'));
    $this->assertTransitionData($host_field, $transition_2);
  }

}
