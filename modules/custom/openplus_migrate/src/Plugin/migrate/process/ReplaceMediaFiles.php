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
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;

/**
 * Process the node body and replace media images.
 *
 * @MigrateProcessPlugin(
 *   id = "replace_media_files",
 *   handle_multiples = TRUE
 * )
 * @codingStandardsIgnoreStart
 *
 * To do a link replacement use the following:
 * @code
 * body/value:
 *   plugin: replace_media_files
 *   source: text
 *   migration_uuid: 41ba1708-839f-4fa8-9d8f-8ba452b98534
 * @endcode
 *
 * @codingStandardsIgnoreEnd
 */

class ReplaceMediaFiles extends ProcessPluginBase {
  /**
  * {@inheritdoc}
  */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $mig_uuid = $this->configuration['migration_uuid'];

    $matches = [];
    // match part of file href
    //preg_match_all('/<a href="(\[NODEJSHARVEST_FILE:.*?\])/', $value, $matches, PREG_SET_ORDER);
    preg_match_all('/(\[NODEJSHARVEST_FILE:.*?\])/', $value, $matches, PREG_SET_ORDER);
    /*
     * Returns an array of all matches in the format:
     * array(
     *   0 => array(
     *     0 => "<img src="[NODEJSHARVEST_FILE:file_id]"
     *     1 => "[NODEJSHARVEST_FILE:file_id]"
     */

    if (!empty($matches)) {
      foreach ($matches as $match) {
        list($placeholder, $media_id) = explode(':', str_replace(array('[', ']'),'' , $match[1]));

        $db_table = "migrate_map_maas__mdf__en__" . str_replace('-', '_', $mig_uuid);

        // lookup mid in migrate map
        $connection = \Drupal::database();
        $query = $connection->query("SELECT destid1 FROM {$db_table} where sourceid1 = '$media_id'");
        $results = $query->fetchAll();

        // get link text from harvester
        $uri = ConfigUtil::GetHarvesterBaseUrl() . $mig_uuid . '/media/id/' . $media_id;
        $headers = [
          'Accept' => 'application/json; charset=utf-8',
          'Content-Type' => 'application/json',
        ];

        $request = \Drupal::httpClient()
          ->get($uri, array(
            'headers' => $headers,
        ));

        $response = json_decode($request->getBody());
        $link_text = isset($response->rows[0]->metadata) ? $response->rows[0]->metadata : 'link text';

        if (!empty($results)) {
          $media = Media::load($results[0]->destid1);
          if ($media) {
            $fid = $media->getSource()->getSourceFieldValue($media);
            $file = File::load($fid);
            if ($file) {
              $url = $file->url();
              $rel_url = file_url_transform_relative(file_create_url($url));
              $find = $match[0];

              // The entire replacement string.
              $replacement = ' <a data-entity-substitution="file" data-entity-type="file" ';
              $replacement .= 'data-entity-uuid="' . $file->uuid() . '" ';
              $replacement .= 'href="' . $rel_url . '">' . $link_text . '</a>';

              // Do the actual string replacement.
              $value = str_replace($find, $replacement, $value);
            }
          }
          else {
            // @TODO flag an error that no media entity was loaded 
          }
          // @TODO flag an error that the media was not found in migration map 
        }
      }
    }

    return $value;
  }

}
