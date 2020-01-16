<?php

namespace Drupal\harvest\Plugin\harvest\data_fetcher;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\harvest\DataFetcherFormPluginBase;
use Drupal\harvest\HarvestInterface;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate_plus\Entity\Migration as MigrationEntity;

/**
 * Provides basic authentication for the HTTP resource.
 *
 * @DataFetcherForm(
 *   id = "http",
 *   title = @Translation("Http"),
 *   parent = "http"
 * )
 */
class Http extends DataFetcherFormPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\harvest\Entity\Harvest $entity */
    $entity = $form_state->getBuildInfo()['callback_object']->getEntity();
    $default_value = $entity->dataFetcherSettings['http']['url'] ?? '';
    if ($entity instanceof MigrationEntity) {
      $source = $entity->get('source');
      $default_value = $source['urls'] ?: '';
    }
    return [
      'url' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#default_value' => $default_value,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getParserData(array $form, FormStateInterface $form_state) {
    return $form_state->getValue([$this->getPluginId(), 'url']);
  }

  public function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $source = $entity->get('source')?:[];
    $source[''] = '';
  }

  /**
   * {@inheritdoc}
   */
  public function alterMigration(HarvestInterface $importer, Migration $migration) {
    if (!empty($importer->dataFetcherSettings['http']['url'])) {
      $source_config = $migration->getSourceConfiguration();
      $source_config['urls'] = $importer->dataFetcherSettings['http']['url'];
      $migration->set('source', $source_config);
    }
  }

}
