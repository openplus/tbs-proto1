<?php
namespace Drupal\openplus_migrate\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\openplus_migrate\Util\ConfigUtil;

/**
 *
 * @RestResource(
 *   id = "openplus_media_add",
 *   label = @Translation("Migration Media ADD"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/add-media-mig",
 *   }
 * )
 */
class OpenplusAddMediaMigration extends ResourceBase {

  public function post($vars) {
    // base values
    $values = array();
    $uuid = str_replace('-', '_', $vars['uuid']); // Replace - with _ for db compatibility
    $label = $vars['label'];
    $values['id'] = 'maas__mdf__en__' . str_replace('-', '_', $vars['uuid']);
    $values['class'] = 'Drupal\migrate\Plugin\Migration';
    $values['migration_group'] = 'maas__group__' . $uuid;
    $values['migration_tags'] = null;
    $values['migration_dependencies'] = null;
    $values['label'] = $label;
      //Harvested Media

    // Source
    $values['source'] = [
      'plugin' => 'url',
      'data_fetcher_plugin' => 'http',
      'data_parser_plugin' => 'json',
      'item_selector' => 'rows/',  // added the trailing slash
      'ids' => ['file_id' => ['type' => 'string']], // our json has ids like 7 and 7_fr so I think this is correct but not sure
      'headers' => [
        'Accept' => 'application/json; charset=utf-8',
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer 8c336cb91315d25ac6a2ff43d2975d1a'
      ],
      'urls' => [
        ConfigUtil::GetHarvesterBaseUrl() . $vars['uuid'] . '/media',
      ],
      'fields' => [
        ['name' => 'file_id', 'label' => 'Harvestedn file ID', 'selector' => 'id'],
        ['name' => 'file_name', 'label' => 'File name', 'selector' => 'filename_uuid'],
        ['name' => 'file_path', 'label' => 'File path', 'selector' => 'filename'],
        ['name' => 'file_url', 'label' => 'URI', 'selector' => 'url'],
      ],
      'constants' => [
        'is_public' => 'true', 
        'file_destination' => 'public://migrated', 
        'harvest_url' => ConfigUtil::GetHarvesterDomain(),
      ],
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
    $values['process']['filename'] = 'file_name';
    $values['process']['destination_full_path'] = [
      [
        'plugin' => 'concat',
        'delimiter' => '/',
        'source' => [
          'constants/file_destination',
          'file_name',
        ],
      ],
      [
        'plugin' => 'urlencode',
      ] 
    ];

    $values['process']['harvest_media_path'] = [
      'plugin' => 'substr',
      'source' => 'file_path',
      'start' => 36,
    ];

    $values['process']['harvest_source'] = [
      'plugin' => 'concat',
      'delimiter' => '/',
      'source' => [
        'constants/harvest_url',
        '@harvest_media_path',
      ],
    ];

    $values['process']['uri'] = [
      'plugin' => 'download',
      'source' => ['@harvest_source', '@destination_full_path'],
      'file_exists' => 'rename',
    ];

    $values['process']['langcode'] = [
      'plugin' => 'default_value',
      'default_value' => 'en'
    ];

    $migration = Migration::create($values);
    $migration->save();
    $response = ['message' => sprintf('Created migration ID: %s with label: %s', $migration->id(), $migration->label())];

    return new ResourceResponse($response);
  }
}
