<?php

namespace Drupal\library_manager\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\library_manager\LibraryDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Library manager settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The library discovery service.
   *
   * @var \Drupal\library_manager\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * Constructs the form object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\library_manager\LibraryDiscoveryInterface $library_discovery
   *   The discovery service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LibraryDiscoveryInterface $library_discovery) {
    parent::__construct($config_factory);
    $this->libraryDiscovery = $library_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('library_manager.library_discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'library_manager_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['library_manager.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $settings = $this->config('library_manager.settings')->get();

    $form['libraries_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Libraries path'),
      '#description' => $this->t('A local file system path where library files will be stored. This directory should not be writable by web server nor accessible over the web.'),
      '#after_build' => ['system_check_directory'],
      '#default_value' => $settings['libraries_path'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('library_manager.settings')
      ->set('libraries_path', $form_state->getValue('libraries_path'))
      ->save();
    $this->libraryDiscovery->clearCachedDefinitions();
    parent::submitForm($form, $form_state);
  }

}
