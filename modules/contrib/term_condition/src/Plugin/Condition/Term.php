<?php

/**
 * @file
 * Contains \Drupal\term_condition\Plugin\Condition\Term.
 */

namespace Drupal\term_condition\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\taxonomy\Entity\Term as CoreTerm;

/**
* Provides a 'Term' condition to enable a condition based in module selected status.
*
* @Condition(
*   id = "term",
*   label = @Translation("Term"),
*   context = {
*     "node" = @ContextDefinition("entity:node", required = TRUE , label = @Translation("node"))
*   }
* )
*
*/
class Term extends ConditionPluginBase {
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Creates a new ExampleCondition instance.
   *
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['tid'] = array(
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Select a taxonomy term'),
      '#default_value' => isset($this->configuration['tid']) ? CoreTerm::load($this->configuration['tid']) : NULL,
      '#target_type' => 'taxonomy_term',
    );

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['tid'] = $form_state->getValue('tid');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['tid']) && !$this->isNegated()) {
      return TRUE;
    }

    $node = $this->getContextValue('node');

    foreach ($node->referencedEntities() as $referenced_entity) {
      if ($referenced_entity->getEntityTypeId() == 'taxonomy_term'
        && $referenced_entity->id() == $this->configuration['tid']) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary()
  {
    $tid = $this->configuration['tid'];

    if (!empty($this->configuration['negate'])) {
      return $this->t('The node is not associated with taxonomy term @tid.', array('@tid' => $tid));
    }
    else {
      return $this->t('The node is associated with taxonomy term @tid.', array('@tid' => $tid));
    }
 }

}
