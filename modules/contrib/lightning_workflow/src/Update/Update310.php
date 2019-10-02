<?php

namespace Drupal\lightning_workflow\Update;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Update("3.1.0")
 */
final class Update310 implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  private $moduleInstaller;

  /**
   * The config factory service.
   *
   * @var ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Update310 constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ModuleInstallerInterface $module_installer, ConfigFactoryInterface $config_factory) {
    $this->moduleInstaller = $module_installer;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer'),
      $container->get('config.factory')
    );
  }

  /**
   * Enables the Moderation Dashboard module.
   *
   * @update
   *
   * @ask Do you want to enable the Moderation Dashboard module?
   *
   * @param \Symfony\Component\Console\Style\StyleInterface $io
   *   The I/O handler.
   */
  public function enableModerationDashboard(StyleInterface $io) {
    $installed = $this->moduleInstaller->install(['moderation_dashboard']);

    if ($installed) {
      $question = (string) $this->t('Do you want users with the appropriate permissions to be redirected to the moderation dashboard when they log in?');

      $this->configFactory
        ->getEditable('moderation_dashboard.settings')
        // Disable the redirect by default, to preserve the previous behavior.
        ->set('redirect_on_login', $io->confirm($question, FALSE))
        ->save();
    }
  }

}
