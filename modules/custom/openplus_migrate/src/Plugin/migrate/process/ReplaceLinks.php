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
 * Process the node body and replace link UUID's with linkit formatted links.
 *
 * @MigrateProcessPlugin(
 *   id = "replace_links",
 *   handle_multiples = TRUE
 * )
* @codingStandardsIgnoreStart
 *
 * To do a link replacement use the following:
 * @code
 * body/value:
 *   plugin: replace_links
 *   source: text
 *   migration_uuid: 41ba1708-839f-4fa8-9d8f-8ba452b98534
 * @endcode
 *
 * @codingStandardsIgnoreEnd
 */

class ReplaceLinks extends ProcessPluginBase {

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
    //preg_match_all('/<a href="(\[NODEJSHARVEST_LINK:.*?\])/', $value, $matches, PREG_SET_ORDER);
    preg_match_all('/(\[NODEJSHARVEST_LINK:.*?\])/', $value, $matches, PREG_SET_ORDER);
    /*
     * Returns an array of all matches in the format:
     * array(
     *   0 => array(
     *     0 => "<a href="[NODEJSHARVEST_LINK:a1b11aad-9ca2-4ecf-8b19-29c4d2bb5e40]"
     *     1 => "[NODEJSHARVEST_LINK:a1b11aad-9ca2-4ecf-8b19-29c4d2bb5e40]"
     */

    if (!empty($matches)) {
      foreach ($matches as $match) {
        list($placeholder, $link_uuid) = explode(':', str_replace(array('[', ']'),'' , $match[1]));
        // get source_url from nodejs EP using UUID
        $uri = ConfigUtil::GetHarvesterBaseUrl() . $mig_uuid . '/links/id/' . $link_uuid;

        $headers = [
          'Accept' => 'application/json; charset=utf-8',
          'Content-Type' => 'application/json',
        ];

        $request = \Drupal::httpClient()
          ->get($uri, array(
            'headers' => $headers,
          ));

        $response = json_decode($request->getBody());
        // only do replacement if we found a link
        if (isset($response->rows[0]->link)) {
          $source_url = $response->rows[0]->link;
          $link_text = isset($response->rows[0]->metadata) ? $response->rows[0]->metadata : 'link text';

          // see if we have the node migrated
          $query = \Drupal::entityQuery('node');
          $query->condition('field_source_url', $source_url, '=');
          $results = $query->execute();

          if (!empty($results)) {
            $node = Node::load(array_pop($results));

            $find = $match[0];
            // The entire replacement string.
            $replacement = '<a data-entity-substitution="canonical"';
            $replacement .= ' data-entity-type="node"';
            $replacement .= ' data-entity-uuid="' . $node->uuid() . '"';
            $replacement .= ' href="/node/' . $node->id() . '">' . $link_text . '</a>';
            // Do the actual string replacement.
            $value = str_replace($find, $replacement, $value);
          }
          else {
            // @TODO flag an error that no node was found for the link
          }
        }
        else {
          // @TODO flag an error that the link was not found in the harvest
        }
      }
    }

    return $value;
  }

}
