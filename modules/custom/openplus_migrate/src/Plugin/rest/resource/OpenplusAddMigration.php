<?php
namespace Drupal\openplus_migrate\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\migrate_plus\Entity\Migration;

/**
 *
 * @RestResource(
 *   id = "openplus_migrate_add",
 *   label = @Translation("Migration ADD"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/add-migration",
 *   }
 * )
 */
class OpenplusAddMigration extends ResourceBase {
  
  public function post($vars) {    
    //  gid, uuid, label (from migration node), json_file?
    $values = array();

    // base values
    $values['uuid'] = $vars['uuid'];
    // Replace - with _ for db compatibility
    $values['id'] = 'migration_' . str_replace('-', '_', $vars['uuid']) . '_' . $vars['type'] . '_' . $vars['lang'];
    $values['class'] = 'Drupal\migrate\Plugin\Migration';
    $values['migration_group'] = 'maas_group_' . $vars['uuid'];
    $values['migration_tags'] = null;
    $values['migration_dependencies'] = null;
    $values['label'] = $vars['label'];

    switch ($vars['type']){

      //Harvested Nodes
      case 'page':
        // Source
        $values['source'] = [
          'plugin' => 'url',
          'data_fetcher_plugin' => 'http',
          'data_parser_plugin' => 'json',
          // based on source plugin example: https://www.lullabot.com/articles/pull-content-from-a-remote-drupal-8-site-using-migrate-and-json-api
          'item_selector' => 'rows/',  // added the trailing slash
          'ids' => ['id' => ['type' => 'string']], // our json has ids like 7 and 7_fr so I think this is correct but not sure
          'headers' => [
            'Accept' => 'application/json; charset=utf-8',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer 8c336cb91315d25ac6a2ff43d2975d1a'
          ],
          'urls' => [
            //'http://migrate.openplus.ca:3000/nodejs/export/41ba1708-839f-4fa8-9d8f-8ba452b98534/page',
            'http://migrate.openplus.ca:3000/nodejs/export/' . $vars['uuid'] . '/' . $vars['type'] . '/' . $vars['lang'],
          ],
          'fields' => [
            ['name' => 'id', 'label' => 'ID', 'selector' => 'id'],
            ['name' => 'title', 'label' => 'Title', 'selector' => 'title'],
            ['name' => 'body', 'label' => 'Body', 'selector' => 'body'],
            ['name' => 'language', 'label' => 'Language', 'selector' => 'language'],
          ]
        ];

        // Destination
        $values['destination'] = [
          'plugin' => 'entity:node',
        ];

        // Process
        $values['process'] = array();
        $values['process']['type'] = [
          'plugin' => 'default_value',
          'default_value' => 'page'
        ];
        $values['process']['uid'] = [
          'plugin' => 'default_value',
          'default_value' => 1,
        ];

        $values['process']['title'] = 'title';
        $values['process']['body/value'] = 'body';
        $values['process']['body/format'] = [
          'plugin' => 'default_value',
          'default_value' => 'rich_text'
        ];
        $values['process']['langcode'] = [
          'plugin' => 'default_value',
          'default_value' => 'en'
        ];

        break;

      //Harvested Media
      case 'media':

        // Source
        $values['source'] = [
          'plugin' => 'url',
          'data_fetcher_plugin' => 'http',
          'data_parser_plugin' => 'json',
          // based on source plugin example: https://www.lullabot.com/articles/pull-content-from-a-remote-drupal-8-site-using-migrate-and-json-api
          'item_selector' => 'rows/',  // added the trailing slash
          'ids' => ['fid' => ['type' => 'string']], // our json has ids like 7 and 7_fr so I think this is correct but not sure
          'headers' => [
            'Accept' => 'application/json; charset=utf-8',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer 8c336cb91315d25ac6a2ff43d2975d1a'
          ],
          'urls' => [
            //'http://migrate.openplus.ca:3000/nodejs/export/41ba1708-839f-4fa8-9d8f-8ba452b98534/page',
            'http://migrate.openplus.ca:3000/nodejs/export/' . $vars['uuid'] . '/' . $vars['type'],
          ],
          'fields' => [
            ['name' => 'fid', 'label' => 'File ID', 'selector' => 'id'],
            ['name' => 'filename', 'label' => 'File Name', 'selector' => 'filename_uuid'],
            ['name' => 'uri', 'label' => 'URI', 'selector' => 'filename'],
          ]
        ];

        // Destination
        $values['destination'] = [
          'plugin' => 'entity:file',
        ];

        // Process
        $values['process'] = array();

        $values['process']['uid'] = [
          'plugin' => 'default_value',
          'default_value' => 1,
        ];
        $values['process']['filename'] = 'filename';
        $values['process']['uri'] = 'uri';
        $values['process']['langcode'] = [
          'plugin' => 'default_value',
          'default_value' => 'en'
        ];

        break;

    }

    $migration = Migration::create($values);
    $migration->save();

    $response = ['message' => 'Created migration ID: ' . $migration->id() . ' with label: ' . $migration->label()];
    //$response = ['message' => 'Done'];
    return new ResourceResponse($response);

  }
}
