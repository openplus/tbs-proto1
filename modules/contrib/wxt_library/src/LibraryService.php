<?php

namespace Drupal\wxt_library;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LibraryService {

  /**
   * @var string
   */
  protected $libraryName;

  /**
   * @var string
   */
  protected $libraryPath;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'));
  }

  /**
   * Constructs a new NodePreviewForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;

    $config = $this->configFactory->get('wxt_library.settings');
    $wxt_active = $config->get('wxt.theme');
    $this->libraryPath = _wxt_library_get_path($wxt_active, TRUE);

    $wxt_active = str_replace('-', '_', $wxt_active);
    $wxt_active = str_replace('theme_', '', $wxt_active);
    $this->libraryName = $wxt_active;
  }

  /**
   * Return the name of the library.
   */
  public function getLibraryName() {
    return $this->libraryName;
  }

  /**
   * Return the location of the library.
   */
  public function getLibraryPath() {
    return $this->libraryPath;
  }

}
