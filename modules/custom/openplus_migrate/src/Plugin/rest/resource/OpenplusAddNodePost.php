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
 *   id = "openplus_node_post_add",
 *   label = @Translation("Post process nodes"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/add-node-post",
 *   }
 * )
 */
class OpenplusAddNodePost extends ResourceBase {

  public function post($vars) {
    //  gid, uuid, label (from migration node), json_file?
    $values = array();

    // base values
    // Replace - with _ for db compatibility
    $uuid = str_replace('-', '_', $vars['uuid']);
    $values['id'] = 'maas__ndp__en__' . $uuid;
    $values['class'] = 'Drupal\migrate\Plugin\Migration';
    $values['migration_group'] = 'maas__group__' . str_replace('-', '_', $vars['uuid']);
    $values['migration_tags'] = null;
    $values['migration_dependencies'] = null;
    $values['label'] = $vars['label'];

    // Source
    $values['source'] = [
      'plugin' => 'post_process_node',
      'migration_uuid' => $vars['uuid'],
      'target' => 'default',
      'key' => 'default',
    ];

    // Destination
    $values['destination'] = [
      'plugin' => 'entity:node',
    ];
    // Process
    $values['process'] = array();
    $values['process']['id'] = [
      [
        'plugin' => 'op_migration_lookup',
        'migration' => 'maas__nd__en__' . $uuid,
        'source' => 'id',
      ],
      [
        'plugin' => 'skip_on_lock',
        'method' => 'row',
      ],
    ];

    $values['process']['title'] = 'title';
    $values['process']['nid'] = 'nid';
    $values['process']['vid'] = 'vid';
    $values['process']['uid'] = 'uid';
    $values['process']['type'] = 'type';
    $values['process']['langcode'] = 'langcode';

    $values['process']['body/value'] = [
      [
        'plugin'         => 'replace_tables',
        'migration_uuid' => $vars['uuid'],
        'source'         => 'body'
      ],
      [
        'plugin'         => 'replace_links',
        'migration_uuid' => $vars['uuid'],
      ],
      [
        'plugin'         => 'replace_media_images',
        'migration_uuid' => $vars['uuid'],
      ],
      [
        'plugin'         => 'replace_media_files',
        'migration_uuid' => $vars['uuid'],
      ],
    ];

    $values['process']['body/format'] = [
      'plugin' => 'default_value',
      'default_value' => 'rich_text'
    ];

    $migration = Migration::create($values);
    $migration->save();

    $response = ['message' => 'Created migration ID: ' . $migration->id() . ' with label: ' . $migration->label()];

    return new ResourceResponse($response);

  }
}
