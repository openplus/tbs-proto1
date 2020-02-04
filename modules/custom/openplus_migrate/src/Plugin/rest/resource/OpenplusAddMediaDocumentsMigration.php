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
 *   id = "openplus_media_documents_add",
 *   label = @Translation("Migration Media Documents ADD"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/add-media-documents-mig",
 *   }
 * )
 */
class OpenplusAddMediaDocumentsMigration extends ResourceBase {

  public function post($vars) {
    $uuid = str_replace('-', '_', $vars['uuid']); // Replace - with _ for db compatibility
    $label = $vars['label'];

    $media_migration_name = "maas__mdf__$uuid";

    $values = [
      'id' => "maas__mdd__en__$uuid",
      'class' => 'Drupal\migrate\Plugin\Migration',
      'migration_tags' => null,
      'migration_group' => "maas__group__$uuid",
      /*
            'dependencies' => [
              'enforced' => [
                'module' => ['openplus_migrate']
              ]
            ],
      */
      'label' => "{$label} - documents",
      'source' => [
        'plugin' => 'media_document',
        'key' => 'default', // <--
        'target' => 'default', // <--
        'migration_uuid' => $uuid,
      ],
      'process' => [
        'mid' => 'fid',
        'vid' => 'fid',
        'bundle' => [ // Media bundle
          'plugin' => 'default_value',
          'default_value' => 'document',
        ],
        'name' => 'filename', // Use title as media name.
        'status' => [
          'plugin' => 'default_value',
          'default_value' => 1
        ],
        'langcode' => 'language',
        'field_document/target_id' => 'fid', 
        'content_translation_source' => [
          'plugin' => 'default_value',
          'default_value' => 'en',
        ],
      ],
      'destination' => [
        'plugin' => 'entity:media'
      ],
      /*
            'migration_dependencies' => [
              'required' => [
                $media_migration_name,
              ]
            ]
      */
    ];

    $migration = Migration::create($values);
    $migration->save();
    $response = ['message' => sprintf('Created migration ID: %s with label: %s', $migration->id(), $migration->label())];

    return new ResourceResponse($response);
  }
}
