<?php

namespace Drupal\wxt_library\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a 'Panels Title' condition.
 *
 * @Condition(
 *   id = "panels_title",
 *   label = @Translation("Panels Title"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Current Node"), required = FALSE),
 *     "taxonomy_term" = @ContextDefinition("entity:taxonomy_term", label = @Translation("Current Taxonomy Term"), required = FALSE),
 *   }
 * )
 */
class PanelsTitle extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a PanelsTitle condition plugin.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(RequestStack $request_stack, array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('request_stack'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Do not display on panelized page(s)'),
      '#default_value' => $this->configuration['enabled'],
      '#description' => $this->t('Disables the display on panelized page(s).'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['enabled'] = $form_state->getValue('enabled');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {

    // Page Manager support for Panels.
    $request = $this->requestStack->getCurrentRequest();
    $page_manager = $request->attributes->get('page_manager_page');
    if (!empty($page_manager) && $page_manager->access('view')) {
      $variants = $page_manager->getVariants();
      foreach ($variants as $variant) {
        if ($variant->access('view')) {
          /** @var \Drupal\ctools\Plugin\BlockVariantInterface $variant_plugin */
          $variant_plugin = $variant->getVariantPlugin();
          foreach ($variant_plugin->getRegionAssignments() as $blocks) {
            /** @var \Drupal\Core\Block\BlockPluginInterface[] $blocks */
            foreach ($blocks as $block) {
              if ($block->getPluginId() == 'page_title_block') {
                return FALSE;
              }
            }
          }
        }
      }
    }

    // Panelizer support for Node.
    $node = $this->getContextValue('node');
    if (!empty($node) && $node->__isset('panelizer')) {
      $panelizer = $node->get('panelizer');

      $panelizer_values = $node->get('panelizer')->getValue();
      $view_mode = array_pop($panelizer_values);
      $view_mode = $view_mode['view_mode'];

      $panelizer = \Drupal::service('panelizer');

      $displays = $panelizer->getDefaultPanelsDisplays($node->getEntityTypeId(), $node->bundle(), $view_mode);
      if (!array_key_exists('default', $displays)) {
        return FALSE;
      }
      $display = $displays['default'];

      $display = $panelizer->getEntityViewDisplay($node->getEntityTypeId(), $node->bundle(), $view_mode);
      $render_display = $display->collectRenderDisplay($node, $view_mode);
      $content = $render_display->get('content');

      if (isset($content['title'])) {
        return FALSE;
      }
    }

    // Panelizer support for Taxonomy Term.
    $taxonomy_term = $this->getContextValue('taxonomy_term');
    if (!empty($taxonomy_term) && $taxonomy_term->__isset('panelizer')) {

      $panelizer = $taxonomy_term->get('panelizer');

      $panelizer_values = $taxonomy_term->get('panelizer')->getValue();

      $view_mode = array_pop($panelizer_values);
      $view_mode = $view_mode['view_mode'];

      $panelizer = \Drupal::service('panelizer');

      $displays = $panelizer->getDefaultPanelsDisplays($taxonomy_term->getEntityTypeId(), $taxonomy_term->bundle(), $view_mode);
      if (!array_key_exists('default', $displays)) {
        return FALSE;
      }
      $display = $displays['default'];

      $display = $panelizer->getEntityViewDisplay($taxonomy_term->getEntityTypeId(), $taxonomy_term->bundle(), $view_mode);
      $render_display = $display->collectRenderDisplay($taxonomy_term, $view_mode);
      $content = $render_display->get('content');

      if (isset($content['name'])) {
        return FALSE;
      }
    }

    return TRUE;

  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    if (empty($this->configuration['enabled'])) {
      return t('Enabled');
    }
    return t('Disabled');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['enabled' => FALSE] + parent::defaultConfiguration();
  }

}
