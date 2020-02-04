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
 *   id = "openplus_migrategroup_delete",
 *   label = @Translation("Migration group DELETE"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/delete-migration-group",
 *   }
 * )
 */
class OpenplusDeleteMigrationGroup extends ResourceBase {

  public function post($vars) {
    $id = 'maas__group__' . str_replace('-', '_', $vars['uuid']);

    $migrationGroup = MigrationGroup::load($id);

    if ($migrationGroup) {
      $migrationGroup->delete();
      $response = ['message' => 'Deleted migration group ID: ' . $id];
      return new ResourceResponse($response);
    }

    $response = ['message' => 'Migration group ID ' . $id . ' not found.'];
    return new ResourceResponse($response, 500);
  }
}
