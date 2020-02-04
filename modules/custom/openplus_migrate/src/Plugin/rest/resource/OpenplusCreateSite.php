<?php
namespace Drupal\openplus_migrate\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Filesystem\Filesystem;
use Drupal\Core\Database\Driver\mysql\Connection;

/**
 *
 * @RestResource(
 *   id = "openplus_site_create",
 *   label = @Translation("Site CREATE"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/create-project-site",
 *   }
 * )
 */
class OpenplusCreateSite extends ResourceBase {

  public function post($vars) {
    $message = array();

    // prefix: project, wxtproto, webform, etc.
    $domain =  \Drupal::request()->getHost();
    $subsite = $vars['prefix'] . $vars['id'];  
    $root = '/var/www/html/' . $domain . '/html/';
    $site = $domain . '.' . $subsite;
    $site_dir = $root . '/sites/' . $site;
    $message[] = $site_dir;

    // only allow this from main site and not from subsites
    $site_path = \Drupal::service('site.path');
    if ($site_path == 'sites/default') {

      $fs = new Filesystem();
      // Create new directory
      if (!$fs->exists($site_dir)) {
        try {
          $fs->mkdir($site_dir . '/files');
          $message[] =  "Successfully created directory: " . $site_dir;
        } catch (IOExceptionInterface $exception) {
          $message[] =  "An error occurred while creating directory: " . $exception->getPath();
        }

        // symlink site in root back to root
        $sym_link = $root . $subsite;
        $ouput = array();
        $errors = array();
        exec("ln -s $root $sym_link", $output, $errors); //friggin symfony would not do this @TODO debug
        if (!empty($errors)) {
          $message[] = implode(',', $errors);
        }
        else {
          $message[] = 'Site link: ' . $sym_link . ' created successfully.';
        }

        // token replace database name
        $settings_file = file_get_contents($root . '/base.settings.tpl');
        $settings_file = str_replace("_SITENAME_", $subsite, $settings_file);
        file_put_contents($site_dir . '/settings.php', $settings_file);
        $fs->chmod($site_dir . '/settings.php', 0666);
        // create service.yml file
        $fs->copy($root . '/base.services.yml', $site_dir . '/services.yml');
  
        // create the database
        $options = [
          'host' => '127.0.0.1',
          'port' => '3306',
          'username' => 'root',
          'password' => 'iaJiey7ue|Doh%u6Ae$7aeZ8',
        ];
  
        $connection = Connection::open($options);
        $connection->query("CREATE DATABASE `" . $subsite . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
        $connection->query("GRANT ALL ON " . $subsite . ".* TO 'prod1can'@'localhost';");
        $connection->query("FLUSH PRIVILEGES;");
        $ouput = array();
        $errors = array();
        exec("mysql -h 127.0.0.1 --user=root --password='iaJiey7ue|Doh%u6Ae$7aeZ8' " . $subsite . " < /var/www/html/" . $domain . "/html/default_db.sql", $output, $errors);
        if (!empty($errors)) {
          $message[] = implode(',', $errors);
        }
        else {
          $message[] = 'Database: ' . $subsite . ' created successfully.';
        }
      }
    }

    $response = ['message' => implode('. ', $message)];

    return new ResourceResponse($response, 200);
  }

}
