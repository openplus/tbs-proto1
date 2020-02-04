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
 *   id = "openplus_migrategroup_update",
 *   label = @Translation("Migration group UPDATE"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/update-migration-group",
 *   }
 * )
 */
class OpenplusUpdateMigrationGroup extends ResourceBase {

  public function post($vars) {
    $id = 'maas__group__' . str_replace('-', '_', $vars['uuid']);

    $migrationGroup = MigrationGroup::load($id);

    $httpCode = 500; // default HTTP code in case something goes wrong
    if ($migrationGroup) {
      $migrationGroup->set('label', $vars['label']);
      if ($migrationGroup->save() === SAVED_UPDATED) {
        $response = ['message' => 'Updated label of migration group ID: ' . $id . ' to: ' . $vars['label']];
        $httpCode = 200;
      }
      else
        $response = ['message' => 'Failed updating label of migration group ID: ' . $id . ' to: ' . $vars['label']];
    }
    else
      $response = ['message' => 'Migration group ID ' . $id . ' not found.'];

    return new ResourceResponse($response, $httpCode);
  }
}
