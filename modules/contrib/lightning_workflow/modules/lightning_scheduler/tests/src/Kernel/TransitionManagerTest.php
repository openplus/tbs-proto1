<?php

namespace Drupal\Tests\lightning_scheduler\Kernel;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_scheduler\TransitionManager;

/**
 * @coversDefaultClass \Drupal\lightning_scheduler\TransitionManager
 *
 * @group lightning
 * @group lightning_workflow
 * @group lightning_scheduler
 */
class TransitionManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_moderation',
    'datetime',
    'lightning_scheduler',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('system');

    // In order to prove that time zones are normalized correctly, set the
    // system default and Drupal default time zones differently.
    date_default_timezone_set('UTC');
    $this->config('system.date')
      ->set('timezone.default', 'America/New_York')
      ->save();
  }

  /**
   * @covers ::validate
   *
   * @dataProvider providerValidate
   */
  public function testValidate($value, $expect_errors) {
    $element = [
      '#value' => Json::encode($value),
      '#name' => 'test_element',
      '#parents' => ['test_element'],
    ];
    $form_state = new FormState();

    $form_state->setFormObject($this->prophesize(FormInterface::class)->reveal());

    TransitionManager::validate($element, $form_state);

    $this->assertSame($expect_errors, FormState::hasAnyErrors());
  }

  /**
   * Data provider for ::testValidate().
   *
   * @return array
   */
  public function providerValidate() {
    return [
      'empty string' => [
        '',
        TRUE,
      ],
      'null' => [
        NULL,
        TRUE,
      ],
      'boolean false' => [
        FALSE,
        TRUE,
      ],
      'boolean true' => [
        TRUE,
        TRUE,
      ],
      'random string' => [
        $this->randomString(128),
        TRUE,
      ],
      'integer' => [
        123,
        TRUE,
      ],
      'empty array' => [
        [],
        FALSE,
      ],
      'time' => [
        [
          'when' => '08:57',
        ],
        TRUE,
      ],
      'date' => [
        [
          [
            'state' => 'fubar',
            'when' => '1984-09-19',
          ],
        ],
        TRUE,
      ],
      'date and time' => [
        [
          [
            'when' => '1938-37-12 08:57',
          ],
        ],
        TRUE,
      ],
      'valid different time stamps, invalid order' => [
        [
          [
            'state' => 'fubar',
            'when' => mktime(15, 42, 0, 11, 5, 2018),
          ],
          [
            'state' => 'fubar',
            'when' => mktime(2, 30, 0, 9, 4, 2018),
          ],
        ],
        TRUE,
      ],
      'valid same dates, valid times, invalid order' => [
        [
          [
            'state' => 'fubar',
            'when' => mktime(6, 30, 0, 9, 19, 2022),
          ],
          [
            'state' => 'fubar',
            'when' => mktime(4, 46, 0, 9, 19, 2022),
          ],
        ],
        TRUE,
      ],
      'valid different dates' => [
        [
          [
            'state' => 'fubar',
            'when' => mktime(2, 30, 0, 9, 4, 2022),
          ],
          [
            'state' => 'fubar',
            'when' => mktime(15, 42, 0, 11, 5, 2022),
          ],
        ],
        FALSE,
      ],
      'valid same dates, different times' => [
        [
          [
            'state' => 'fubar',
            'when' => mktime(2, 30, 0, 9, 19, 2022),
          ],
          [
            'state' => 'fubar',
            'when' => mktime(15, 42, 0, 9, 19, 2022),
          ],
        ],
        FALSE,
      ],
    ];
  }

}
