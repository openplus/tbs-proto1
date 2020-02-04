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
 *   id = "openplus_media_images_add",
 *   label = @Translation("Migration Media Images ADD"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/add-media-images-mig",
 *   }
 * )
 */
class OpenplusAddMediaImagesMigration extends ResourceBase {

  public function post($vars) {
    // base values
    $uuid = str_replace('-', '_', $vars['uuid']); // Replace - with _ for db compatibility
    $label = $vars['label'];

    $media_migration_name = "maas__mdf__$uuid";

    $values = [
      'id' => "maas__mdi__en__$uuid",
      'class' => 'Drupal\migrate\Plugin\Migration',
      'migration_tags' => null,
      'migration_group' => "maas__group__$uuid",
      'label' => "{$label} - images",
      'source' => [
        'plugin' => 'media_image',
        'key' => 'default', // <--
        'target' => 'default', // <--
        'migration_uuid' => $uuid,
      ],
      'process' => [
        'mid' => 'fid',
        'vid' => 'fid',
        'bundle' => [ // Media bundle
          'plugin' => 'default_value',
          'default_value' => 'image',
        ],
        'name' => 'filename', // Use image title as media name.
        'status' => [
          'plugin' => 'default_value',
          'default_value' => 1
        ],
        'langcode' => 'language',
        'image/target_id' => 'fid',
        'image/title' => 'title',
        'image/alt' => [
          'plugin' => 'image_alt_text',
        ],

        'content_translation_source' => [
          'plugin' => 'default_value',
          'default_value' => 'en',
        ]
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
