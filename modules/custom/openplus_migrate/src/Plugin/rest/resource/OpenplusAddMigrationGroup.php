<?php
namespace Drupal\openplus_migrate\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\migrate_plus\Entity\MigrationGroup;

/**
 *
 * @RestResource(
 *   id = "openplus_migrategroup_add",
 *   label = @Translation("Migration group ADD"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/add-migration-group",
 *   }
 * )
 */
class OpenplusAddMigrationGroup extends ResourceBase {
  
  public function post($vars) {    
    $values = array();

    // base values
    $values['id'] = 'maas__group__' . str_replace('-', '_', $vars['uuid']);
    $values['label'] = $vars['label'];

    $migration = MigrationGroup::create($values);
    $migration->save();

    $response = ['message' => 'Created migration group ID: ' . $migration->id() . ' with label: ' . $migration->label()];

    return new ResourceResponse($response);
  }
}
