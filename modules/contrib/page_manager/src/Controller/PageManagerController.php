<?php

namespace Drupal\page_manager\Controller;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\page_manager\PageVariantInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controllers for Page Manager.
 */
class PageManagerController extends ControllerBase {
  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * Route title callback.
   *
   * @param mixed $page_manager_page_variant
   *
   * @return string
   *   The title for a particular page.
   */
  public function pageTitle($page_manager_page_variant) {
    if (is_string($page_manager_page_variant)) {
      $page_manager_page_variant = $this->entityManager->loadEntityByConfigTarget('page_variant', $page_manager_page_variant);
    }

    // Get the variant context.
    $contexts = $page_manager_page_variant->getContexts();
    // Get the variant page entity.

    $tokens = [];

    foreach ($contexts as $key => $context){
      $tokens[$key] = $context->getContextValue();
    }

    // Get the page variant page title setting.
    $variant_title_setting = $page_manager_page_variant->getPageTitle();
    // Load the Token service and run our page title through it.
    $token_service = \Drupal::token();
    return $token_service->replace($variant_title_setting,
      $tokens);
  }

}
