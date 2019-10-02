<?php

namespace Drupal\taxonomy\ContextProvider;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Sets the current Taxonomy Term as a context on Taxonomy routes.
 */
class TaxonomyRouteContext implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new NodeRouteContext.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    $result = [];
    $context_definition = new ContextDefinition('entity:taxonomy_term', NULL, FALSE);
    $value = NULL;

    if (($route_object = $this->routeMatch->getRouteObject()) && ($route_contexts = $route_object->getOption('parameters')) && isset($route_contexts['taxonomy_term'])) {

      if ($term = $this->routeMatch->getParameter('taxonomy_term')) {
        $value = $term;
      }
    }
    elseif ($this->routeMatch->getRouteName() == 'taxonomy_term.add') {
      $vocab = $this->routeMatch->getParameter('vocabulary');
      $value = Term::create(['type' => $vocab->id()]);
    }

    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['route']);

    $context = new Context($context_definition, $value);
    $context->addCacheableDependency($cacheability);
    $result['taxonomy_term'] = $context;

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $context = new Context(new ContextDefinition('entity:taxonomy_term', $this->t('Taxonomy Term from URL')));
    return ['taxonomy_term' => $context];
  }

}
