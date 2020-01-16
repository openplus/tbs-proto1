<?php

namespace Drupal\openplus_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrateProcessInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\openplus_migrate\Util\ConfigUtil;

/**
 * Process the node body and replace table UUID's with linkit formatted tables.
 *
 * @MigrateProcessPlugin(
 *   id = "replace_tables",
 *   handle_multiples = TRUE
 * )
* @codingStandardsIgnoreStart
 *
 * To do a table replacement use the following:
 * @code
 * body/value:
 *   plugin: replace_tables
 *   source: text
 *   migration_uuid: 41ba1708-839f-4fa8-9d8f-8ba452b98534
 * @endcode
 *
 * @codingStandardsIgnoreEnd
 */

class ReplaceTables extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    if (!isset($configuration['migration_uuid'])) {
      throw new \InvalidArgumentException('The "migration uuid" must be provided.');
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
  * {@inheritdoc}
  */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // get the UUID from config to call the correct harvest DB/endpoint
    $mig_uuid = $this->configuration['migration_uuid'];

    $matches = [];
    preg_match_all('/(\[NODEJSHARVEST_TABLE:.*?\])/', $value, $matches, PREG_SET_ORDER);
    /*
     * Returns an array of all matches in the format:
     * array(
     *   0 => array(
     *     0 => '<table src="[NODEJSHARVEST_TABLE:d2a25ca3-2de0-4603-b4b9-44a8d4b08aa6]" />'
     *     1 => '[NODEJSHARVEST_TABLE:d2a25ca3-2de0-4603-b4b9-44a8d4b08aa6]'
     */

    if (!empty($matches)) {
      foreach ($matches as $match) {
        list($placeholder, $table_uuid) = explode(':', str_replace(array('[', ']'),'' , $match[1]));
        // get source_url from nodejs EP using UUID

        $uri = ConfigUtil::GetHarvesterBaseUrl() . $mig_uuid . '/table/id/' . $table_uuid;

        $headers = [
          'Accept' => 'application/json; charset=utf-8',
          'Content-Type' => 'application/json',
        ];

        $request = \Drupal::httpClient()
          ->get($uri, array(
            'headers' => $headers,
          ));

        $response = json_decode($request->getBody());
        // only do replacement if we found a table
        if (isset($response->rows[0]->table_markup)) {
          $markup = $response->rows[0]->table_markup;

          $find = $match[0];
          // The entire replacement string.
          $replacement = $markup;
          // Do the actual string replacement.
          $value = str_replace($find, $replacement, $value);
        }
        else {
          // @TODO flag an error that the table was not found in the harvest
        }
      }
    }

    return $value;
  }

}
