<?php

namespace Drupal\openplus_migrate\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\openplus_migrate\Util\ConfigUtil;
use Drupal\Core\Cache\Cache;


/**
 * Class PreImportEvent
 *
 * @package Drupal\openplus_migrate\EventSubscriber
 */
class MigrateImportEvents implements EventSubscriberInterface {

  /**
   * @return mixed
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::PRE_IMPORT] = ['preImport', 0];
    $events[MigrateEvents::POST_IMPORT] = ['postImport', 0];
    return $events;
  }

  /**
   * @param $event
   */
  public function preImport(MigrateImportEvent $event) {
    $migration = $event->getMigration();
    \Drupal::logger('openplus_migrate')->notice('Pre-import on: ' . $migration->id());
    
    if ($migration->id() == 'migration_cleanup') {
      $links = [];
      $migrations = \Drupal::entityQuery('migration_group')
        ->execute();
      foreach ($migrations as $migration) {
        if (strpos($migration, 'maas__group') !== FALSE) {
          $mig_uuid = str_replace('_', '-', str_replace('maas__group__', '', $migration));
          $uri = ConfigUtil::GetHarvesterBaseUrl() . $mig_uuid . '/links';
       
          $headers = [
            'Accept' => 'application/json; charset=utf-8',
            'Content-Type' => 'application/json',
          ];

          $request = \Drupal::httpClient()
            ->get($uri, array(
              'headers' => $headers,
            ));

          $response = json_decode($request->getBody());
          if (!empty($response->rows)) {
            foreach ($response->rows as $row) {
              $links[$row->id] = $row;
            }
          }
        } // if maas_group
      } // migrations loop


      // Check if the cache already contain data.
      //if ($item = \Drupal::cache()->get($cid)) {
      //return $item->data;
      //}

      // Set the cache
      $cid = 'openplus_migrate:harvested_links';
      \Drupal::cache()->set($cid, $links, Cache::PERMANENT); 



    } // if migration_cleanup
  }

  public function postImport(MigrateImportEvent $event) {
    $migration = $event->getMigration();
    \Drupal::logger('openplus_migrate')->notice('Post-import on: ' . $migration->id());

    if ($migration->id() == 'migration_cleanup') {
      $cid = 'openplus_migrate:harvested_links';
      \Drupal::cache()->set($cid, array(), Cache::PERMANENT); 
    }
  }

}
