<?php
namespace Drupal\openplus_migrate\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Filesystem\Filesystem;

/**
 *
 * @RestResource(
 *   id = "openplus_site_delete",
 *   label = @Translation("Migration project (site) DELETE"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/delete-project-site",
 *   }
 * )
 */
class OpenplusDeleteSite extends ResourceBase {

  public function post($vars) {

    $message = array();
    $site_name = $vars['prefix'] . $vars['id'];

    // only allow this from main site 
    $site_path = \Drupal::service('site.path');
    if ($site_path == 'sites/default') {
      //drop the DB
      $dbhost = 'localhost:3306';
      $dbuser = 'root';
      $dbpass = 'iaJiey7ue|Doh%u6Ae$7aeZ8';
      $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
      if (!$conn) {
        $message[] = 'Could not connect to DB: ' . $site_name; 
      }
      else {
        $sql = "DROP DATABASE " . $site_name;
        if (mysqli_query($conn, $sql)) {
          $message[] = 'DB dropped:' . $site_name; 
        }
        else {
          $message[] = 'DB drop failed: ' . $site_name; 
        }
        mysqli_close($conn);
      }

      // Delete directory
      $fs = new Filesystem();
      $domain = \Drupal::request()->getHost();
      $root = '/var/www/html/' . $domain . '/html/';
      $site = $domain . '.' . $site_name;
      $site_dir = $root . 'sites/' . $site;
      if ($fs->exists($site_dir)) {
        $fs->remove($site_dir);
        $message[] = 'Site dir removed: ' . $site_dir; 
      }

      // delete the symlink link
      $sym_link = $root . $site_name;
      if ($fs->exists($sym_link)) {
        $fs->remove($sym_link);
        $message[] = 'Symlink removed: ' . $sym_link; 
      }

    }

    $response = ['message' => implode('. ', $message)];

    return new ResourceResponse($response, 200);
  }
}
