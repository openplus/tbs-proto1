<?php

namespace Drupal\comment\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\comment\CommentLinkBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Handler for showing comment module's entity links.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("comment_entity_link")
 */
class EntityLink extends FieldPluginBase {

  /**
   * Stores the result of node_view_multiple for all rows to reuse it later.
   *
   * @var array
   */
  protected $build;

  /**
   * Comment links builder for a commented entity.
   * @var \Drupal\comment\CommentLinkBuilder
   */
  protected $commentLinkBuilder;

  /**
   * The render object.
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new EntityLink object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\comment\CommentLinkBuilder $commentLinkBuilder
   *   Comment links builder for a commented entity.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The render object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CommentLinkBuilder $commentLinkBuilder, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->commentLinkBuilder = $commentLinkBuilder;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('comment.link_builder'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['teaser'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['teaser'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show teaser-style link'),
      '#default_value' => $this->options['teaser'],
      '#description' => $this->t('Show the comment link in the form used on standard entity teasers, rather than the full entity form.'),
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);

    // Create comment links for the entity.
    $context = ['view_mode' => $this->options['teaser'] ? 'teaser' : 'full'];
    $comment_links = $this->commentLinkBuilder ->buildCommentedEntityLinks($entity, $context);

    return !empty($comment_links['comment__comment']) ? $this->renderer->render($comment_links['comment__comment']) : '';
  }

}
