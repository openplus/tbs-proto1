<?php

namespace Drupal\library_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\library_manager\LibraryDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for library collection routes.
 */
class LibraryCollectionController extends ControllerBase {

  /**
   * The library discovery service.
   *
   * @var \Drupal\library_manager\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * Constructs the controller object.
   *
   * @param \Drupal\library_manager\LibraryDiscoveryInterface $library_discovery
   *   The discovery service.
   */
  public function __construct(LibraryDiscoveryInterface $library_discovery) {
    $this->libraryDiscovery = $library_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('library_manager.library_discovery')
    );
  }

  /**
   * Builds the response.
   */
  public function buildCollection() {

    $build['filters'] = [
      '#type' => 'search',
      '#title' => $this->t('Filter libraries'),
      '#title_display' => 'invisible',
      '#size' => 30,
      '#placeholder' => $this->t('Library name'),
      '#attributes' => [
        'data-drupal-selector' => ['library-filter'],
        'autocomplete' => 'off',
      ],
    ];

    $header = [
      $this->t('Name'),
      $this->t('Version'),
      $this->t('License'),
      $this->t('Definition'),
      $this->t('Operations'),
    ];

    $rows = [];

    foreach ($this->libraryDiscovery->getLibraries() as $library_id => $library_info) {

      $row = [];

      list($extension, $library_name) = explode('/', $library_id);

      $library_parameters = [
        'extension' => $extension,
        'library' => urlencode($library_name),
      ];

      $library_url = new Url('library_manager.library_canonical', $library_parameters);

      $row['name'] = Link::fromTextAndUrl($library_id, $library_url);
      $row['version'] = isset($library_info['version']) ? $library_info['version'] : NULL;
      $row['license'] = isset($library_info['license']['name']) ? $library_info['license']['name'] : NULL;
      $row['definition'] = '';

      if (isset($library_info['definition'])) {
        $row['definition'] = Link::createFromRoute(
          $library_info['definition'],
          'entity.library_definition.edit_form',
          ['library_definition' => $library_info['definition']]
        )->toString();
      }

      $links = [];
      $links['export'] = [
        'title' => $this->t('Export'),
        'url' => Url::fromRoute('library_manager.library_export', $library_parameters),
      ];
      $links['create_definition'] = [
        'title' => $this->t('Create definition'),
        'url' => Url::fromRoute('library_manager.library_build', $library_parameters),
      ];

      $row['links'] = [
        'data' => [
          '#type' => 'operations',
          '#links' => $links,
        ],
      ];

      $rows[] = $row;
    }

    $build['table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => ['data-drupal-selector' => 'library-list'],
    ];

    $build['#attached']['library'][] = 'library_manager/library_manager';

    return $build;
  }

  /**
   * Builds autocomplete response for the library ID.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object that contains the typed keys.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The matched library ID as a JSON response.
   */
  public function buildAutocomplete(Request $request) {

    $matches = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      foreach ($this->libraryDiscovery->getLibraries() as $library_id => $library) {

        if (strpos($library_id, $input) !== FALSE) {
          $matches[] = [
            'label' => $library_id,
            'value' => $library_id,
          ];
        }

      }
    }

    return new JsonResponse($matches);
  }

}
