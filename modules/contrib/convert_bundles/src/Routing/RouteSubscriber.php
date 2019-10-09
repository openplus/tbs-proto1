<?php

namespace Drupal\convert_bundles\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for convert_bundles routes.
 *
 * @see \Drupal\convert_bundles\Plugin\Derivative\ConvertBundlesLocalTask
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      $route = new Route("/$entity_type_id/{{$entity_type_id}}/convertbundles");
      $route
        ->addDefaults([
          '_controller' => '\Drupal\convert_bundles\Controller\EntityController::getEntity',
          '_title' => 'Convert Bundle',
        ])
        ->addRequirements([
          '_permission' => 'administer convert_bundles',
        ])
        ->setOption('_admin_route', TRUE)
        ->setOption('_convert_bundles_entity_type_id', $entity_type_id)
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ]);

      $collection->add("entity.$entity_type_id.convert_bundles", $route);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', 100];
    return $events;
  }

}
