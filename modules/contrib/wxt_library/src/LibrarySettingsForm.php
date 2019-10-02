<?php

namespace Drupal\wxt_library;

use Drupal\Core\Asset\AssetCollectionOptimizerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure wxt_library settings for this site.
 */
class LibrarySettingsForm extends ConfigFormBase {

  /**
   * The render cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $renderCache;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The CSS asset collection optimizer service.
   *
   * @var \Drupal\Core\Asset\AssetCollectionOptimizerInterface
   */
  protected $cssCollectionOptimizer;

  /**
   * The JavaScript asset collection optimizer service.
   *
   * @var \Drupal\Core\Asset\AssetCollectionOptimizerInterface
   */
  protected $jsCollectionOptimizer;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a LibrarySettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Cache\CacheBackendInterface $render_cache
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Asset\AssetCollectionOptimizerInterface $css_collection_optimizer
   *   The CSS asset collection optimizer service.
   * @param \Drupal\Core\Asset\AssetCollectionOptimizerInterface $js_collection_optimizer
   *   The JavaScript asset collection optimizer service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheBackendInterface $render_cache, DateFormatterInterface $date_formatter, AssetCollectionOptimizerInterface $css_collection_optimizer, AssetCollectionOptimizerInterface $js_collection_optimizer, ThemeHandlerInterface $theme_handler) {
    parent::__construct($config_factory);

    $this->renderCache = $render_cache;
    $this->dateFormatter = $date_formatter;
    $this->cssCollectionOptimizer = $css_collection_optimizer;
    $this->jsCollectionOptimizer = $js_collection_optimizer;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('cache.render'),
      $container->get('date.formatter'),
      $container->get('asset.css.collection_optimizer'),
      $container->get('asset.js.collection_optimizer'),
      $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wxt_library_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'wxt_library.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('wxt_library.settings');

    $themes = $this->themeHandler->listInfo();
    $active_themes = [];
    foreach ($themes as $key => $theme) {
      if ($theme->status) {
        $active_themes[$key] = $theme->info['name'];
      }
    }
    // Production or minimized version.
    $form['minimized'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Use Production or development version'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['minimized']['minimized_options'] = [
      '#type' => 'radios',
      '#title' => $this->t('Choose minimized or non-minimized version.'),
      '#options' => [
        0 => $this->t('Use non-minimized libraries (Development)'),
        1 => $this->t('Use minimized libraries (Production)'),
      ],
      '#default_value' => $config->get('minimized.options'),
    ];

    // Production or minimized version.
    $form['wxt_library'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('WxT Theme Selection'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['wxt_library']['wxt_theme'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select the specific WxT theme you would like to use.'),
      '#options' => _wxt_library_options(),
      '#default_value' => $config->get('wxt.theme'),
    ];

    // Per-theme visibility.
    $form['theme'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Themes Visibility'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['theme']['theme_visibility'] = [
      '#type' => 'radios',
      '#title' => $this->t('Activate on specific themes'),
      '#options' => [
        0 => $this->t('All themes except those listed'),
        1 => $this->t('Only the listed themes'),
      ],
      '#default_value' => $config->get('theme.visibility'),
    ];
    $form['theme']['theme_themes'] = [
      '#type' => 'select',
      '#title' => 'List of themes where library will be loaded.',
      '#options' => $active_themes,
      '#multiple' => TRUE,
      '#default_value' => $config->get('theme.themes'),
      '#description' => $this->t("Specify in which themes you wish the library to load."),
    ];

    // Per-path visibility.
    $form['url'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Activate on specific URLs'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['url']['url_visibility'] = [
      '#type' => 'radios',
      '#title' => $this->t('Load WxT Bootstrap on specific pages'),
      '#options' => [
        0 => $this->t('All pages except those listed'),
        1 => $this->t('Only the listed pages'),
      ],
      '#default_value' => $config->get('url.visibility'),
    ];
    $form['url']['url_pages'] = [
      '#type' => 'textarea',
      '#title' => '<span class="element-invisible">' . $this->t('Pages') . '</span>',
      '#default_value' => _wxt_library_array_to_string($config->get('url.pages')),
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %wildcard for every personal blog. %front is the front page.",
        ['%blog' => 'blog', '%wildcard' => 'blog/*', '%front' => '<front>']),
    ];

    // Files settings.
    $form['files'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Files Settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['files']['types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select which type of file(s) to load from the library. By default you should check both, however in some cases you might need to load only CSS or JS WxT Bootstrap files.'),
      '#options' => [
        'css' => $this->t('CSS files'),
        'js' => $this->t('Javascript files'),
      ],
      '#default_value' => $config->get('files.types'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('wxt_library.settings')
      ->set('wxt.theme', $form_state->getValue('wxt_theme'))
      ->set('theme.visibility', $form_state->getValue('theme_visibility'))
      ->set('theme.themes', $form_state->getValue('theme_themes'))
      ->set('url.visibility', $form_state->getValue('url_visibility'))
      ->set('url.pages', _wxt_library_string_to_array($form_state->getValue('url_pages')))
      ->set('minimized.options', $form_state->getValue('minimized_options'))
      ->set('files.types', $form_state->getValue('types'))
      ->save();

    $this->cssCollectionOptimizer->deleteAll();
    $this->jsCollectionOptimizer->deleteAll();

    // This form allows page compression settings to be changed, which can
    // invalidate cached pages in the render cache, so it needs to be cleared on
    // form submit.
    $this->renderCache->deleteAll();
  }

}
