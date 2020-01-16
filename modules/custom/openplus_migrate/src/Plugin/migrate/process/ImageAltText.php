<?php

namespace Drupal\openplus_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\openplus_migrate\Util\ConfigUtil;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "image_alt_text"
 * )
 */
class ImageAltText extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($source, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
//    die(print_r($row->source, true));
    $migration_uuid = str_replace('_', '-', $row->getSourceProperty('migration_uuid'));
    $file_id = $row->getSourceProperty('filename');
    $file_id = substr($file_id, 0, strrpos( $file_id, '.'));;

    $uri = ConfigUtil::GetHarvesterBaseUrl() . $migration_uuid . '/media/id/' . $file_id;

    $headers = [
      'Accept' => 'application/json; charset=utf-8',
      'Content-Type' => 'application/json',
    ];

    $request = \Drupal::httpClient()
      ->get($uri, array(
        'headers' => $headers,
      ));

    $response = json_decode($request->getBody());
    // TODO: Handle translated Alt Text instead of picking the first item
    return $response->rows[0]->metadata
      ? substr($response->rows[0]->metadata, 0, 512)
      : '';
  }
}
