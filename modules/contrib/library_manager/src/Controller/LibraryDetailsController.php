<?php

namespace Drupal\library_manager\Controller;

use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\library_manager\LibraryDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns responses a single library pages.
 */
class LibraryDetailsController extends ControllerBase {

  /**
   * The library discovery service.
   *
   * @var \Drupal\library_manager\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * The serialization service.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializer;

  /**
   * Constructs the controller object.
   *
   * @param \Drupal\library_manager\LibraryDiscoveryInterface $library_discovery
   *   The discovery service.
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   The serialization service.
   */
  public function __construct(LibraryDiscoveryInterface $library_discovery, SerializationInterface $serializer) {
    $this->libraryDiscovery = $library_discovery;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('library_manager.library_discovery'),
      $container->get('serialization.yaml')
    );
  }

  /**
   * Builds the response.
   */
  public function details($extension, $library) {

    $library_info = $this->libraryDiscovery->getLibraryByName($extension, urldecode($library));
    if (!$library_info) {
      throw new NotFoundHttpException();
    }

    $build['#title'] = $library;

    $build['name'] = [
      '#type' => 'item',
      '#title' => $this->t('Name'),
      '#markup' => $library,
    ];

    $build['extension'] = [
      '#type' => 'item',
      '#title' => $this->t('Extension'),
      '#markup' => $extension,
    ];

    if (isset($library_info['definition'])) {
      $build['definition'] = [
        '#type' => 'item',
        '#title' => $this->t('Definition'),
        '#markup' => Link::createFromRoute(
          $library_info['definition'],
          'entity.library_definition.edit_form',
          ['library_definition' => $library_info['definition']]
        )->toString(),
      ];
    }

    if (!empty($library_info['remote'])) {
      $remote_url = Url::fromUri($library_info['remote']);
      $build['remote'] = [
        '#type' => 'item',
        '#title' => $this->t('Remote URL'),
        '#markup' => Link::fromTextAndUrl($library_info['remote'], $remote_url)->toString(),
      ];
    }

    if (!empty($library_info['version'])) {
      $build['version'] = [
        '#type' => 'item',
        '#title' => $this->t('Version'),
        '#markup' => $library_info['version'],
      ];
    }

    if (!empty($library_info['license']['name'])) {
      $build['license'] = [
        '#type' => 'item',
        '#title' => $this->t('License'),
        '#markup' => $library_info['license']['name'],
      ];
    }

    if ($library_info['js']) {
      $links = [];
      foreach ($library_info['js'] as $file) {
        $file_name = $file['data'];
        $file_uri = $file['type'] == 'file' ? 'base://' . $file_name : $file_name;

        $links[$file_name] = [
          'title' => $file_name,
          'url' => Url::fromUri($file_uri),
        ];
      }
      $build['js'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('JS'),
        'links' => [
          '#theme' => 'links',
          '#links' => $links,
        ],
      ];
    }

    if ($library_info['css']) {
      $links = [];
      foreach ($library_info['css'] as $file) {
        $file_name = $file['data'];
        $file_uri = $file['type'] == 'file' ? 'base://' . $file_name : $file_name;

        $links[$file_name] = [
          'title' => $file_name,
          'url' => Url::fromUri($file_uri),
        ];
      }
      $build['css'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('CSS'),
        'links' => [
          '#theme' => 'links',
          '#links' => $links,
        ],
      ];
    }

    if ($library_info['dependencies']) {
      $links = [];
      foreach ($library_info['dependencies'] as $dependency) {
        list($extension, $library_name) = explode('/', $dependency);
        $links[$dependency] = [
          'title' => $dependency,
          'url' => Url::fromRoute(
            'library_manager.library_canonical',
            ['extension' => $extension, 'library' => $library_name]
          ),
        ];
      }
      $build['dependencies'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Dependencies'),
        'links' => [
          '#theme' => 'links',
          '#links' => $links,
        ],
      ];
    }

    // Find dependent libraries.
    $required_by = [];
    $current_library_id = $extension . '/' . $library;
    foreach ($this->libraryDiscovery->getLibraries() as $library_id => $library_info) {
      foreach ($library_info['dependencies'] as $dependency) {
        if ($dependency == $current_library_id) {
          $required_by[] = $library_id;
        }
      }
    }

    if (count($required_by) > 0) {
      $links = [];
      foreach ($required_by as $dependent_library) {
        list($extension, $library_name) = explode('/', $dependent_library);
        $links[$dependent_library] = [
          'title' => $dependent_library,
          'url' => Url::fromRoute(
            'library_manager.library_canonical',
            ['extension' => $extension, 'library' => $library_name]
          ),
        ];
      }
      $build['required_by'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Required by'),
        'links' => [
          '#theme' => 'links',
          '#links' => $links,
        ],
      ];
    }

    return $build;
  }

  /**
   * Returns library info in YML format.
   */
  public function export($extension, $library) {

    $library_info = $this->libraryDiscovery->exportLibraryByName($extension, urldecode($library));
    if (!$library_info) {
      throw new NotFoundHttpException();
    }

    $build['#title'] = $library;

    $build['data'] = [
      '#type' => 'textarea',
      '#value' => $this->serializer->encode($library_info),
      '#attributes' => [
        'class' => ['library-export'],
      ],
      '#codemirror' => [
        'mode' => 'yaml',
        'readOnly' => TRUE,
        'toolbar' => FALSE,
      ],
    ];

    return $build;
  }

}
