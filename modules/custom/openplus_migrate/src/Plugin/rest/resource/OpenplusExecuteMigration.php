<?php
namespace Drupal\openplus_migrate\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate_tools\MigrateBatchExecutable;
use Drupal\openplus_migrate\Util\MigrationUtil;

/**
 *
 * @RestResource(
 *   id = "openplus_migrate_execute",
 *   label = @Translation("Migration EXECUTE"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "https://www.drupal.org/link-relations/create" = "/api/v1/execute-migration",
 *   }
 * )
 */
class OpenplusExecuteMigration extends ResourceBase {

  public function post($vars) {

    $id = 'maas__' . $vars['type'] . '__' . $vars['lang'] . '__' . str_replace('-', '_', $vars['uuid']);
    $migration = \Drupal::service('plugin.manager.migration')->createInstance($id);

    $result = false; // default

    if ($migration) {
      $migration->getIdMap()->prepareUpdate();
      $executable = new \Drupal\migrate_tools\MigrateExecutable($migration, new \Drupal\migrate\MigrateMessage());
      $dependencies = $migration->getMigrationDependencies();
      // set the update to true for any dependent migrations
      //$update = empty($dependencies['required']) ? FALSE : TRUE;
      //$migrateMessage = new MigrateMessage();  @TODO we need this if / when we do batch

      switch ($vars['op']) {
        case 'import':
          $result = $executable->import();
          $options = [
            'limit' => 0,
            'update' => FALSE,
            'force' => 0,
          ];

          $executable = new MigrateBatchExecutable($migration, new MigrateMessage(), $options);
          $executable->batchImport();
          // TODO: determine result in case of batch import.
        break;
        case 'rollback':
          $result = $executable->rollback();
          /*
          $options = [
            'limit' => $limit,
            'update' => $update,
            'force' => $force,
          ];

          $executable = new MigrateBatchExecutable($migration, $migrateMessage, $options);
          $executable->rollback();
          */
        break;
        case 'stop':
          $migration->interruptMigration(MigrationInterface::RESULT_STOPPED);
          // TODO: determine result
        break;
        case 'reset':
          $migration->setStatus(MigrationInterface::STATUS_IDLE);
          // TODO: determine result
        break;
      }

      $response['stat'] = MigrationUtil::GetMigrationStat($migration);
    }

    $response['op'] = $vars['op'];
    $response['type'] = $vars['type'];
    $response['lang'] = $vars['lang'];

    if ($result == MigrationInterface::RESULT_COMPLETED) {
      $response['success'] = true;
      $response['message'] = sprintf("Migration operation %s completed successfully.", $vars['op']);
      return new ResourceResponse($response);
    }
    else {
      $response['success'] = false;
      $response['message'] = sprintf("Migration operation %s failed.", $vars['op']);
      return new ResourceResponse($response, 500);
    }
  }
}
