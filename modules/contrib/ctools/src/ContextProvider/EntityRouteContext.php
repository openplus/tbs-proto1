<?php

namespace Drupal\ctools\ContextProvider;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Sets the current entity as a context on their equivalent routes.
 */
class EntityRouteContext implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Entity Type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EntityRouteContext.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    $result = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      $value = NULL;
      $context_definition = new ContextDefinition('entity:' . $entity_type_id, NULL, FALSE);
      if (($route_object = $this->routeMatch->getRouteObject()) && ($route_contexts = $route_object->getOption('parameters')) && isset($route_contexts[$entity_type_id])) {
        if ($entity = $this->routeMatch->getParameter($entity_type_id)) {
          $value = $entity;
        }
      }

      $cacheability = new CacheableMetadata();
      $cacheability->setCacheContexts(['route']);

      $context = new Context($context_definition, $value);
      $context->addCacheableDependency($cacheability);
      $result[$entity_type_id] = $context;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $contexts = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      $contexts[$entity_type_id] = new Context(new ContextDefinition('entity:' . $entity_type_id, $this->t('@label from URL', [
        '@label' => $entity_type->getLabel(),
      ])));
    }
    return $contexts;
  }

}
