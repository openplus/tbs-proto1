<?php

namespace Drupal\openplus_migrate\Plugin\migrate\process;

use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Extracts a value from an array.
 *
 * Available configuration keys:
 * - source: The input value - must be an array.
 * - key: The key to access the value.
 * - default: (optional) A default value to assign to the destination if the
 *   key does not exist.
 *
 * Examples:
 *
 * @code
 * process:
 *   new_text_field:
 *     plugin: extract
 *     source: some_text_field
 *     key: some_key 
 * @endcode
 *
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "openplus_extract",
 *   handle_multiples = TRUE
 * )
 */
class OpenplusExtract extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      throw new MigrateException('Input should be an array.');
    }
    //$new_value = NestedArray::getValue($value, $this->configuration['index'], $key_exists);
    $key_exists = FALSE;
    foreach ($value as $field) {
      if ($field[0] == $this->configuration['key']) {
        $new_value = $field[1];
        $key_exists = TRUE;
        break;
      }
    }

    if (!$key_exists) {
      if (isset($this->configuration['default'])) {
        $new_value = $this->configuration['default'];
      }
      else {
        $new_value = NULL; 
      }
    }
    return $new_value;
  }

}
