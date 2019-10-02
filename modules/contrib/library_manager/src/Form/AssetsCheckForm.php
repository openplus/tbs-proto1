<?php

namespace Drupal\library_manager\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\library_manager\LibraryDiscoveryInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a assets report form.
 */
class AssetsCheckForm extends FormBase {

  /**
   * The library discovery service.
   *
   * @var \Drupal\library_manager\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The date.formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a AssetsReportForm object.
   *
   * @param \Drupal\library_manager\LibraryDiscoveryInterface $library_discovery
   *   The discovery service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The formatter service.
   */
  public function __construct(LibraryDiscoveryInterface $library_discovery, StateInterface $state, DateFormatterInterface $date_formatter) {
    $this->libraryDiscovery = $library_discovery;
    $this->state = $state;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('library_manager.library_discovery'),
      $container->get('state'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assets_report_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $last_check = $this->state->get('library_manager_assets_check_timestamp');

    $message = $last_check ?
      $this->t('Last check: @last_check ago.', ['@last_check' => $this->dateFormatter->formatTimeDiffSince($last_check)]) :
      $this->t('Last check: never.');

    $form['date'] = [
      '#type' => 'item',
      '#markup' => $message,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Check assets'),
    ];

    return $form;
  }

  /**
   * Checks assets and reloads the page.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $operations = [];
    $processCallback = [__CLASS__, 'processBatch'];
    foreach ($this->libraryDiscovery->getLibraries() as $library_info) {
      foreach (['css', 'js'] as $asset_type) {
        foreach ($library_info[$asset_type] as $file) {
          if ($file['type'] == 'file') {
            $operations[] = [$processCallback, [file_create_url($file['data'])]];
          }
          elseif ($file['type'] == 'external') {
            $operations[] = [$processCallback, [$file['data']]];
          }
          else {
            $this->messenger()->addStatus($this->t('Unknown file type %type.', ['%type' => $file['type']]));
          }
        }
      }
    }

    $batch = [
      'init_message' => $this->t('Preparing assets list...'),
      'operations' => $operations,
      'finished' => [__CLASS__, 'finishBatch'],
    ];

    batch_set($batch);
  }

  /**
   * Batch process callback.
   */
  public static function processBatch($url, $context) {

    $context['message'] = $url;
    try {
      \Drupal::httpClient()->get($url);
      $context['results'][] = TRUE;
    }
    catch (GuzzleException $exception) {
      $url = Url::fromUri($url, ['attributes' => ['target' => '_blank']]);
      $link = new Link($url->toString(), $url);
      \Drupal::messenger()->addWarning(t('Could not load @link.', ['@link' => $link->toString()]));
      $context['results'][] = FALSE;
    }

  }

  /**
   * Batch finish callback.
   */
  public static function finishBatch($success, $results) {
    \Drupal::state()->set('library_manager_assets_check_timestamp', time());
    $loaded = count(array_filter($results));
    $total = count($results);
    $message_type = $loaded == $total ? 'status' : 'warning';
    \Drupal::messenger()->addMessage(t('Loaded @loaded of @total.', ['@loaded' => $loaded, '@total' => $total]), $message_type);
  }

}
