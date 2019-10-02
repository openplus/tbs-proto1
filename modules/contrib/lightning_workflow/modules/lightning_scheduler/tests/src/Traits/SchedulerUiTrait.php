<?php

namespace Drupal\Tests\lightning_scheduler\Traits;

use Behat\Mink\Exception\ElementNotFoundException;
use Drupal\Component\Serialization\Json;

/**
 * Contains methods for interacting with the scheduler UI.
 */
trait SchedulerUiTrait {

  /**
   * Sets the time zone.
   *
   * Functional tests normally run in the Syndey, Australia time zone in order
   * to catch time zone-related edge cases and bugs. However, the scheduler UI
   * is extremely sensitive to time zones, so to reduce craziness it's best to
   * set it to the time zone configured in php.ini.
   *
   * @param string $default
   *   (optional) The time zone to set if none is configured in php.ini.
   *   Defaults to UTC.
   */
  protected function setUpTimeZone($default = 'UTC') {
    $this->config('system.date')->clear('timezone.default')->save();
    date_default_timezone_set(ini_get('date.timezone') ?: $default);
  }

  /**
   * Creates a scheduled state transition.
   *
   * @param string $to_state
   *   The label of the state to transition to.
   * @param int $ts
   *   The localized time stamp at which the transition should take place. This
   *   should be generated using mktime(), not gmmktime().
   * @param bool $save
   *   (optional) Whether to save the transition, or just enter it into the UI
   *   without saving. Defaults to TRUE.
   */
  protected function createTransition($to_state, $ts, $save = TRUE) {
    $assert = $this->assertSession();

    try {
      $assert->elementExists('named', ['link', 'add another'])->click();
    }
    catch (ElementNotFoundException $e) {
      $assert->elementExists('named', ['link', 'Schedule a status change'])->click();
    }

    $assert->fieldExists('Scheduled moderation state')->selectOption($to_state);
    $assert->fieldExists('Scheduled transition date')->setValue(date('mdY', $ts));
    $assert->fieldExists('Scheduled transition time')->setValue(date('h:i:sA', $ts));

    if ($save) {
      $assert->buttonExists('Save transition')->press();

      $text = sprintf(
        "Change to $to_state on %s at %s",
        date('F j, Y', $ts),
        date('g:i A', $ts)
      );
      $assert->pageTextContains($text);
    }
    $this->addToAssertionCount(1);
  }

  /**
   * Sets the scheduled transition data.
   *
   * @param string $field
   *   The hidden field in which to store the transitions.
   * @param array[] $data
   *   The scheduled transitions.
   */
  protected function setTransitionData($field, array $data) {
    $data = Json::encode($data);
    $this->assertSession()->hiddenFieldExists($field)->setValue($data);
  }

  /**
   * Asserts that a set of transitions is present.
   *
   * @param string $field
   *   The hidden field which contains the transitions.
   * @param array[] $data
   *   The scheduled transitions.
   */
  protected function assertTransitionData($field, array $data) {
    array_walk($data, function (array &$transition) {
      $transition['when'] = gmdate('c', $transition['when']);
    });
    $this->assertSession()->hiddenFieldValueEquals($field, Json::encode($data));
  }

  /**
   * Sets the time input's step attribute.
   *
   * @param int $time_step
   *   (optional) The time step.
   */
  protected function setTimeStep($time_step = 1) {
    $this->config('lightning_scheduler.settings')
      ->set('time_step', $time_step)
      ->save();
  }

}
