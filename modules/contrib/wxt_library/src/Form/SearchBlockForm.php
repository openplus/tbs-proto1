<?php

namespace Drupal\wxt_library\Form;

use Drupal\bootstrap\Bootstrap;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\search\SearchPageRepositoryInterface;
use Drupal\wxt_library\LibraryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the search form for the search block.
 */
class SearchBlockForm extends FormBase {

  /**
   * The search page repository.
   *
   * @var \Drupal\search\SearchPageRepositoryInterface
   */
  protected $searchPageRepository;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Drupal\wxt_library\LibraryService definition.
   *
   * @var \Drupal\wxt_library\LibraryService
   */
  protected $wxtLibraryServiceWxT;

  /**
   * Constructs a new SearchBlockForm.
   *
   * @param \Drupal\search\SearchPageRepositoryInterface $search_page_repository
   *   The search page repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\wxt_library\LibraryService $wxt_library_service_wxt
   *   The LibraryService.
   */
  public function __construct(
    SearchPageRepositoryInterface $search_page_repository,
    ConfigFactoryInterface $config_factory,
    RendererInterface $renderer,
    LibraryService $wxt_library_service_wxt
  ) {
    $this->searchPageRepository = $search_page_repository;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
    $this->wxtLibraryServiceWxT = $wxt_library_service_wxt;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('search.search_page_repository'),
      $container->get('config.factory'),
      $container->get('renderer'),
      $container->get('wxt_library.service_wxt')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wxt_search_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $wxt_active = $this->wxtLibraryServiceWxT->getLibraryName();

    // Set up the form to submit using GET to the correct search page.
    $entity_id = $this->searchPageRepository->getDefaultSearchPage();
    if (!$entity_id) {
      $form['message'] = [
        '#markup' => $this->t('Search is currently disabled'),
      ];
      return $form;
    }

    $route = 'search.view_' . $entity_id;
    $submit_title = $this->t('Search');

    $form['#action'] = Url::fromRoute($route)->toString();
    $form['#method'] = 'get';

    $form['keys'] = [
      '#id' => 'wb-srch-q',
      '#type' => 'search',
      '#title' => '',
      '#title_display' => 'invisible',
      '#size' => 27,
      '#maxlength' => 128,
      '#default_value' => '',
      '#placeholder' => '',
      '#attributes' => [
        'title' => $this->t('Enter the terms you wish to search for.'),
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $submit_title,
      '#id' => 'wb-srch-sub',
    ];

    if ($wxt_active == 'gcweb' || $wxt_active == 'gcweb_legacy') {
      $form['submit']['#value'] = '';
      $form['keys']['#placeholder'] = $this->t('Search website');
      $wxt_gcweb_search = Bootstrap::getTheme()->getSetting('wxt_gcweb_search');
      if (!empty($wxt_gcweb_search)) {
        $form['#action'] = $this->t('https://www.canada.ca/en/sr/srb.html#wb_land');
        $form['#method'] = 'get';

        $form['keys']['#placeholder'] = $this->t('Search Canada.ca');
        $form['keys']['#name'] = 'q';

        $form['cdn'] = [
          '#name' => 'cdn',
          '#value' => 'canada',
          '#type' => 'hidden',
          '#input' => 'TRUE',
        ];
        $form['st'] = [
          '#name' => 'st',
          '#value' => 's',
          '#type' => 'hidden',
          '#input' => 'TRUE',
        ];
        $form['num'] = [
          '#name' => 'num',
          '#value' => '10',
          '#type' => 'hidden',
          '#input' => 'TRUE',
        ];
        $form['langs'] = [
          '#name' => 'langs',
          '#value' => 'eng',
          '#type' => 'hidden',
          '#input' => 'TRUE',
        ];
        $form['st1rt'] = [
          '#name' => 'st1rt',
          '#value' => '0',
          '#type' => 'hidden',
          '#input' => 'TRUE',
        ];
        $form['s5bm3ts21rch'] = [
          '#name' => 's5bm3ts21rch',
          '#value' => 'x',
          '#type' => 'hidden',
          '#input' => 'TRUE',
        ];
      }
    }

    // SearchPageRepository::getDefaultSearchPage() depends on search.settings.
    $this->renderer->addCacheableDependency($form, $this->configFactory->get('search.settings'));

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This form submits to the search page, so processing happens there.
  }

}
