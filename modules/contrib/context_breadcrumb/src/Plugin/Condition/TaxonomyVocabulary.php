<?php

namespace Drupal\context_breadcrumb\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Taxonomy Vocabulary' condition.
 *
 * @Condition(
 *   id = "taxonomy_vocabulary",
 *   label = @Translation("Taxonomy Vocabulary"),
 *   context = {
 *     "term" = @ContextDefinition("entity:taxonomy_term", label =
 * @Translation("Taxonomy Term"))
 *   }
 * )
 */
class TaxonomyVocabulary extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Creates a new TaxonomyVocabulary instance.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(EntityStorageInterface $entity_storage, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')
        ->getStorage('taxonomy_vocabulary'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $vocabularies = $this->entityStorage->loadMultiple();
    foreach ($vocabularies as $vocabulary) {
      $options[$vocabulary->id()] = $vocabulary->label();
    }
    $form['vocabularies'] = [
      '#title' => $this->t('Taxonomy vocabularies'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->configuration['vocabularies'],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['vocabularies'] = array_filter($form_state->getValue('vocabularies'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (count($this->configuration['vocabularies']) > 1) {
      $vocabularies = $this->configuration['vocabularies'];
      $last = array_pop($vocabularies);
      $vocabularies = implode(', ', $vocabularies);
      return $this->t('The term vocabulary is @vocabularies or @last', [
        '@vocabularies' => $vocabularies,
        '@last' => $last,
      ]);
    }
    $vocabulary = reset($this->configuration['vocabularies']);
    return $this->t('The term vocabulary is @vocabulary', ['@vocabulary' => $vocabulary]);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function evaluate() {
    if (empty($this->configuration['vocabularies']) && !$this->isNegated()) {
      return TRUE;
    }
    $term = $this->getContextValue('term');
    return !empty($this->configuration['vocabularies'][$term->getVocabularyId()]);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['vocabularies' => []] + parent::defaultConfiguration();
  }

}
