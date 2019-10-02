<?php

namespace Drupal\lightning_scheduler;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList;

class TransitionSet {

  /**
   * A set of scheduled transition dates for an entity.
   *
   * @var \Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList
   */
  protected $dateList;

  /**
   * A set of scheduled workflow states for an entity.
   *
   * @var \Drupal\Core\Field\FieldItemListInterface
   */
  protected $stateList;

  /**
   * TransitionSet constructor.
   *
   * @param \Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList $date_list
   *   A set of scheduled transition dates for an entity.
   * @param \Drupal\Core\Field\FieldItemListInterface $state_list
   *   A set of scheduled workflow states for an entity.
   *
   * @throws \InvalidArgumentException if the date list and state list are not
   * of equal length.
   */
  public function __construct(DateTimeFieldItemList $date_list, FieldItemListInterface $state_list) {
    if (count($date_list) !== count($state_list)) {
      throw new \InvalidArgumentException('Transition sets must have equal-length sets of dates and workflow states.');
    }
    $this->dateList = $date_list;
    $this->stateList = $state_list;
  }

  /**
   * Represents the transition set as a map.
   *
   * @return array
   *   The transition set as a flat, sorted map, where the keys are the date and
   *   time of the transition, and the values are the targeted workflow state
   *   ID.
   */
  public function toArray() {
    $data = [];

    foreach ($this->dateList as $delta => $item) {
      $key = $item->date->getTimestamp();
      $data[$key] = $this->stateList[$delta]->value;
    }
    ksort($data);

    return $data;
  }

  /**
   * Represents the transition set as a JSON string.
   *
   * @return string
   *   The transition set as a JSON array of objects, each of which has the
   *   following properties:
   *   - 'state': The targeted workflow state ID.
   *   - 'when': The UTC date and time of the transition in ISO 8601 format.
   */
  public function toJSON() {
    $data = [];

    foreach ($this->dateList as $delta => $item) {
      $data[$delta] = [
        'state' => $this->stateList[$delta]->value,
        'when' => $item->date->format('c', [
          'timezone' => 'UTC',
        ]),
      ];
    }

    return Json::encode($data);
  }

  /**
   * Returns the expected workflow state for a given date and time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $at
   *   The date and time.
   *
   * @return string|null
   *   The expected workflow state for the given date and time. NULL if no
   *   workflow state is targeted.
   */
  public function getExpectedState(DrupalDateTime $at) {
    $at = $at->getTimestamp();

    $data = $this->toArray();

    $filtered_keys = array_filter(array_keys($data), function ($key) use ($at) {
      return $key <= $at;
    });

    if ($filtered_keys) {
      return $data[ end($filtered_keys) ];
    }
    return NULL;
  }

  /**
   * Removes all transitions older than a given date and time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $until
   *   The date and time older than which all transitions will be removed.
   */
  public function trim(DrupalDateTime $until) {
    $until = $until->getTimestamp();

    while (count($this->dateList) > 0 && $this->dateList[0]->date->getTimestamp() < $until) {
      $this->dateList->removeItem(0);
      $this->stateList->removeItem(0);
    }
  }

}
