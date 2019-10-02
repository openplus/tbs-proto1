<?php

namespace Drupal\wxt_library\Controller;

use Drupal\wxt_library\LibraryService;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LibraryController extends ControllerBase {

  /**
   * @var \Drupal\wxt_library\LibraryService
   */
  protected $wxtLibraryService;

  /**
   * {@inheritdoc}
   */
  public function __construct(LibraryService $wxtLibraryService) {
    $this->wxtLibraryService = $wxtLibraryService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('wxt_library.service_wxt')
    );
  }

  /**
   * Information about the active library and location.
   */
  public function wxtInfo() {
    $build['table'] = [
      '#theme' => 'table',
      '#header' => [
        'Library Name',
        'Path Location',
      ],
      '#rows' => [
        [
          'class' => ['row-class'],
          'data' => [
            $this->wxtLibraryService->getLibraryName(),
            [
              'data' => $this->wxtLibraryService->getLibraryPath(),
              'colspan' => 1,
            ],
          ],
        ],
      ],
    ];
    return $build;
  }

}
