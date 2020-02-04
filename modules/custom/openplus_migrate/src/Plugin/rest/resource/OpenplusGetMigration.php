<?php
namespace Drupal\openplus_migrate\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\openplus_migrate\Util\MigrationUtil;

/**
 *
 * @RestResource(
 *   id = "openplus_migrate_get",
 *   label = @Translation("Migration GET"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/get-migration/{uuid}/{type}/{lang}"
 *   }
 * )
 */
class OpenplusGetMigration extends ResourceBase {

  public function get($uuid, $type, $lang) {
    $output = array();

    $id = 'maas__' . $type . '__' . $lang . '__' . str_replace('-', '_', $uuid);
    $migration = \Drupal::service('plugin.manager.migration')->createInstance($id);

    if ($migration) {
      $output = MigrationUtil::GetMigrationStat($migration);
    }

    $build = array(
      '#cache' => array(
        'max-age' => 0,
      ),
    );

    return (new ResourceResponse($output))->addCacheableDependency($build);
  }


}
