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
 *   id = "openplus_get_node_types",
 *   label = @Translation("Content types GET"),
 *   uri_paths = {
 *     "canonical" = "/api/v1/get-content-types"
 *   }
 * )
 */
class OpenplusGetContentTypes extends ResourceBase {

  public function get() {

    $node_types = \Drupal\node\Entity\NodeType::loadMultiple();
    $output = [];
    foreach ($node_types as $node_type) {
      $output[$node_type->id()] = $node_type->label();
    }

    $build = array(
      '#cache' => array(
        'max-age' => 0,
      ),
    );

    return (new ResourceResponse($output))->addCacheableDependency($build);
  }


}
