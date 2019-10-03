<?php

/**
 * @file
 * Contains \Drupal\term_condition\Plugin\Condition\Term.
 */

namespace Drupal\term_condition\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Provides a 'Term' condition to enable a condition based in module selected status.
*
* @Condition(
*   id = "term",
*   label = @Translation("Term"),
*   context = {
*     "node" = @ContextDefinition("entity:node", required = FALSE , label = @Translation("node"))
*   }
* )
*
*/
class Term extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   *  Entity manager instance.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new ExampleCondition instance.
   *
   * @param EntityTypeManagerInterface $entity_manager
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
  public function __construct(EntityTypeManagerInterface $entity_manager, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $container->get('entity_type.manager'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $default_terms = [];

    if(!empty($this->configuration['tid'])) {
      // Load the existing term(s) for the block.
      $terms = $this->configuration['tid'];
      if (!empty($terms)) {
        if (is_array($terms)) {
          foreach ($terms as $key => $term) {
            $term = array_pop($term);
            $default_terms[] = $this->entityTypeManager->getStorage('taxonomy_term')->load($term);
          }
        }
        else {
          $default_terms = $this->entityTypeManager->getStorage('taxonomy_term')->load($terms);
        }
      }
    }

    $form['tid'] = array(
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Select taxonomy term(s)'),
      '#default_value' => $default_terms,
      '#target_type' => 'taxonomy_term',
      '#tags' => TRUE,
    );

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'tid' => NULL,
    ] + parent::defaultConfiguration();
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

    // If configuration['tid'] is an array, there is multiple terms set.
    if(is_array($this->configuration['tid'])) {
      $tids = $this->configuration['tid'];
      unset($this->configuration['tid']);
      foreach ($tids as $tid) {
        $this->configuration['tid'][] = array_pop($tid);
      }
    }

    $entity = $this->getContextValue('node');

    // Not in a node context. Try a few other options.
    if (!$entity) {

      // Potential other ways to try fetch the entity. Assoc array to try get revisions.
      // I wonder if there is a cleaner way to do this?
      // TODO - Provide hook to add extras.
      $potentialRouteMatches = [
        'taxonomy_term' => 'taxonomy_term',
        'node' => 'node_revision',
      ];
      foreach ($potentialRouteMatches as $key => $potentialRouteMatch) {
        $entity = \Drupal::routeMatch()->getParameter($potentialRouteMatch);
        // If the entity extends EntityInterface, we have the entity we want.
        if($entity instanceof EntityInterface) {
          break;
        }
        elseif (is_string($entity)) {
          // If the entity is a string, its likely the revision ID,
          // try load that.
          $entity = $this->entityTypeManager->getStorage($key)->loadRevision($entity);
          break;
        }
      }
      // All checks failed. Stop.
      if (!$entity) {
        return FALSE;
      }
    }
    foreach ($entity->referencedEntities() as $referenced_entity) {
      // If configuration['tid'] is an array with multiple terms, check all
      // tids in the array against the term.
      if(is_array($this->configuration['tid'])) {
        if ($referenced_entity->getEntityTypeId() == 'taxonomy_term'
          && in_array($referenced_entity->id(), $this->configuration['tid'])) {
          return TRUE;
        }
      }
      else {
        if ($referenced_entity->getEntityTypeId() == 'taxonomy_term'
          && $referenced_entity->id() == $this->configuration['tid']) {
          return TRUE;
        }
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
