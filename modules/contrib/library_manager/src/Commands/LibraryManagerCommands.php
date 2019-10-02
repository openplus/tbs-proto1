<?php

namespace Drupal\library_manager\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\library_manager\LibraryDiscoveryInterface;
use Drush\Commands\DrushCommands;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Drush integration for Library manager module.
 *
 * @property \Drush\Log\Logger $logger
 */
class LibraryManagerCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Library discovery.
   *
   * @var \Drupal\library_manager\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * YAML serialization service.
   *
   * @var \Drupal\Component\Serialization\Yaml
   */
  protected $serializer;

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The state backend.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * LibraryManagerCommands constructor.
   *
   * @param \Drupal\library_manager\LibraryDiscoveryInterface $library_discovery
   *   Library discovery.
   * @param \Drupal\Component\Serialization\Yaml $serializer
   *   YAML serializer.
   * @param \GuzzleHttp\Client $http_client
   *   The Guzzle HTTP client.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state store.
   */
  public function __construct(LibraryDiscoveryInterface $library_discovery, Yaml $serializer, Client $http_client, StateInterface $state) {
    $this->libraryDiscovery = $library_discovery;
    $this->serializer = $serializer;
    $this->httpClient = $http_client;
    $this->state = $state;
  }

  /**
   * Displays information about the library in YAML format.
   *
   * @codingStandardsIgnoreStart
   * @command lm:export
   *
   * @param string $library_id
   *   Library ID.
   *
   * @usage drush lm-details core/jquery
   *   Displays detailed information about core/jquery library.
   * @aliases lm-e,lm-export
   * @codingStandardsIgnoreEnd
   */
  public function export($library_id) {
    list ($extension, $library) = array_pad(explode('/', $library_id), 2, NULL);
    if ($extension && $library) {
      $library_info = $this->libraryDiscovery
        ->exportLibraryByName($extension, urldecode($library));
      if ($library_info) {
        $this->io()->write($this->serializer->encode($library_info));
        return;
      }
    }
    throw new \UnexpectedValueException($this->t('Library "@library_id" was not found.', ['@library_id' => $library_id]));
  }

  /**
   * Displays a list of all installed libraries.
   *
   * @command lm:list
   * @aliases lm-l,lm-list
   * @field-labels
   *   name: Name
   *   version: Version
   *   license: License
   * @default-fields name,version
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   List of libraries.
   */
  public function libraryList() {

    $rows = [];
    foreach ($this->libraryDiscovery->getLibraries() as $library_name => $library_info) {
      $rows[$library_name] = [
        'name' => $library_name,
        'version' => isset($library_info['version']) ? $library_info['version'] : '',
        'license' => $library_info['license']['name'],
      ];
    }

    return new RowsOfFields($rows);
  }

  /**
   * Clears library cache.
   *
   * @command lm:cache-clear
   * @aliases lm-cc,lm-cache-clear
   */
  public function cacheClear() {
    $this->libraryDiscovery->clearCachedDefinitions();
    $this->logger->success('Done.');
  }

  /**
   * Check library assets.
   *
   * @command lm:check-assets
   * @aliases lm-ca,lm-check-assets
   */
  public function checkAssets() {

    $total = $loaded = 0;
    $rows = [];

    $progress_bar = $this->io()->createProgressBar();
    $progress_bar->setBarWidth(50);
    $progress_bar->setFormat(' %current% [%bar%] %message%');
    foreach ($this->libraryDiscovery->getLibraries() as $library_info) {
      foreach (['css', 'js'] as $asset_type) {
        foreach ($library_info[$asset_type] as $file) {

          if ($file['type'] == 'file') {
            $url = file_create_url($file['data']);
          }
          elseif ($file['type'] == 'external') {
            $url = $file['data'];
          }
          else {
            throw new \RuntimeException('Unknown file type.');
          }

          $total++;
          $row = [
            '<fg=red;options=bold>Error</>',
            $library_info['name'],
            $url,
          ];

          $progress_bar->setMessage($url);
          $progress_bar->advance();
          try {
            $this->httpClient->get($url);
            $loaded++;
            $row[0] = '<fg=green;options=bold>Found</>';
          }
          catch (GuzzleException $exception) {
            $this->logger->debug($exception->getMessage());
          }
          $rows[] = $row;

        }
      }
    }

    $progress_bar->finish();
    $this->io()->writeln('');

    $headers = [
      'Result',
      'Library',
      'Url',
    ];

    $this->io()->table($headers, $rows);

    $this->state->set('library_manager_assets_check_timestamp', time());

    $message = $this->t('Loaded @loaded of @total.', ['@loaded' => $loaded, '@total' => $total]);
    $message_type = $loaded == $total ? 'success' : 'warning';
    $this->io()->{$message_type}($message);
  }

}
