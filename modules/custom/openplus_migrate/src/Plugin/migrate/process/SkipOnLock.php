<?php

namespace Drupal\openplus_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * If the source evaluates to a configured value, skip processing or whole row.
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_lock"
 * )
 *
 * Available configuration keys:
 * - value: An single value or array of values against which the source value
 *   should be compared.
 * - not_equals: (optional) If set, skipping occurs when values are not equal.
 * - method: What to do if the input value equals to value given in
 *   configuration key value. Possible values:
 *   - row: Skips the entire row.
 *   - process: Prevents further processing of the input property
 *
 * @codingStandardsIgnoreStart
 *
 * Example usage
 * @code
 * process:
 *   id:
 *     -
 *       plugin: migration_lookup
 *       migration: 'maas__nd__en__<uuid>
 *       source: id
 *     -
 *       plugin: skip_on_lock
 *       method: row
 * @endcode
 * The above example will skip the entire row if the content is locked 
 *
 *
 * @codingStandardsIgnoreEnd
 */
class SkipOnLock extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function row($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // check write lock flag on node
    if (empty($value)) {
      return $value;
    }

    $module_handler = \Drupal::service('module_handler');
    if (!$module_handler->moduleExists('flag')) {
      return $value;
    }

    $flag_service = \Drupal::service('flag');
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($value);
    if ($node) {
      //\Drupal::logger('openplus_migrate')->notice('Processing node: ' . $node->id());
      $flag = $flag_service->getFlagById('write_lock');
      // if a flag exists
      if ($flag && $flag->isFlagged($node)) {
        \Drupal::logger('openplus_migrate')->notice('Node is locked, skipping.');
        throw new MigrateSkipRowException();
      }
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function process($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // not sure we need this

    return $value;
  }

}
