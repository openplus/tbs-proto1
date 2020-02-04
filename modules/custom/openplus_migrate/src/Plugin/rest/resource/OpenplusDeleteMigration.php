<?php
namespace Drupal\openplus_migrate\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheableResponseInterface;

/**
 *
 * @RestResource(
 *   id = "openplus_migrate_delete",
 *   label = @Translation("Migration DELETE"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/delete-migration/{id}"
 *   }
 * )
 */
class OpenplusDeleteMigration extends ResourceBase {


}
