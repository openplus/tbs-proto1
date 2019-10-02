<?php

/**
 * @file
 * Drush integration for Library manager module.
 */

use GuzzleHttp\Exception\GuzzleException;

/**
 * Implements hook_drush_command().
 */
function library_manager_drush_command() {

  $items['lm-list'] = [
    'description' => 'Displays a list of all installed libraries.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'aliases' => ['lm-l'],
  ];

  $items['lm-export'] = [
    'description' => 'Displays information about the library in YAML format.',
    'arguments' => ['library_id' => 'Library ID'],
    'required-arguments' => TRUE,
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'aliases' => ['lm-e'],
    'examples' => [
      'drush lm-details core/jquery' => 'Displays detailed information about core/jquery library.',
    ],
  ];

  $items['lm-cache-clear'] = [
    'description' => 'Clears library cache.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'aliases' => ['lm-cc'],
  ];

  $items['lm-check-assets'] = [
    'description' => 'Check library assets.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'aliases' => ['lm-ca'],
  ];

  return $items;
}

/**
 * Callback function for lm-list command.
 */
function drush_library_manager_lm_list() {

  $library_discovery = \Drupal::service('library_manager.library_discovery');

  $rows[] = [
    t('Name'),
    t('Version'),
    t('License'),
  ];

  foreach ($library_discovery->getLibraries() as $library_name => $library_info) {
    $rows[] = [
      $library_name,
      isset($library_info['version']) ? $library_info['version'] : '',
      $library_info['license']['name'],
    ];
  }

  drush_print_table($rows, TRUE);
}

/**
 * Callback function for lm-export command.
 */
function drush_library_manager_lm_export($library_id) {
  list ($extension, $library) = array_pad(explode('/', $library_id), 2, NULL);
  if ($extension && $library) {
    $library_info = Drupal::service('library_manager.library_discovery')
      ->exportLibraryByName($extension, urldecode($library));
    if ($library_info) {
      drush_print(Drupal::service('serialization.yaml')->encode($library_info), 0, NULL, FALSE);
      return;
    }
  }
  drush_set_error(t('Library "@library_id" was not found.', ['@library_id' => $library_id]));
}

/**
 * Callback function for lm-cache-clear command.
 */
function drush_library_manager_lm_cache_clear() {
  \Drupal::service('library.discovery')->clearCachedDefinitions();
}

/**
 * Callback function for lm-check-assets command.
 */
function drush_library_manager_lm_check_assets() {

  $total = $loaded = 0;
  foreach (\Drupal::service('library_manager.library_discovery')->getLibraries() as $library_info) {
    foreach (['css', 'js'] as $asset_type) {
      foreach ($library_info[$asset_type] as $file) {

        if ($file['type'] == 'file') {
          $url = file_create_url($file['data']);
        }
        elseif ($file['type'] == 'external') {
          $url = $file['data'];
        }
        else {
          throw new RuntimeException('Unknown file type.');
        }

        $total++;
        try {
          \Drupal::httpClient()->get($url);
          $loaded++;
        }
        catch (GuzzleException $exception) {
          drush_log(dt('Could not load @url.', ['@url' => $url]), 'warning');
        }

      }
    }
  }

  \Drupal::state()->set('library_manager_assets_check_timestamp', time());
  $message = dt('Loaded @loaded of @total.', ['@loaded' => $loaded, '@total' => $total]);
  $message_type = $loaded == $total ? 'status' : 'warning';
  drush_log($message, $message_type);
}
