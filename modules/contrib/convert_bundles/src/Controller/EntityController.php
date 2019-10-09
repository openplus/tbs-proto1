<?php

namespace Drupal\convert_bundles\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;

/**
 * Controller for convert bundles entity.
 *
 * @see \Drupal\convert_bundles\Routing\RouteSubscriber
 * @see \Drupal\convert_bundles\Plugin\Derivative\ConvertBundlesLocalTask
 */
class EntityController extends ControllerBase {

  /**
   * Tempstorage.
   *
   * @var tempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Session.
   *
   * @var sessionManager
   */
  private $sessionManager;

  /**
   * User.
   *
   * @var currentUser
   */
  protected $currentUser;

  /**
   * Constructs a \Drupal\convert_bundles\Controller\EntityController.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   Temp storage.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   Session.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   User.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, SessionManagerInterface $session_manager, AccountInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user->id();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('session_manager'),
      $container->get('current_user')
    );
  }

  /**
   * Retrieves entity from route match.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity object as determined from the passed-in route match.
   */
  public function getEntity(RouteMatchInterface $route_match) {
    $parameter_name = $route_match->getRouteObject()->getOption('_convert_bundles_entity_type_id');
    $entity = $route_match->getParameter($parameter_name);
    $ids[$entity->id()] = $entity;
    $this->tempStoreFactory->get('convert_bundles_ids')
      ->set($this->currentUser, $ids);

    return $this->redirect('convert_bundles.form', [], ['query' => ['destination' => '/' . $entity->getEntityTypeId() . '/' . $entity->id()]]);
  }

}
