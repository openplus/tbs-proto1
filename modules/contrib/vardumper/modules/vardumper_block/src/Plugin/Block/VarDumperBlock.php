<?php

namespace Drupal\vardumper_block\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Vardumper' block.
 *
 * @Block(
 *   id = "vardumper_block",
 *   admin_label = @Translation("VarDumper"),
 *   category = @Translation("Development")
 * )
 */
class VarDumperBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\Session
   */
  protected $session;

  /**
   * {@inheritDoc}.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SessionInterface $session) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->session = $session;
  }

  /**
   * {@inheritDoc}.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('session')
    );
  }

  /**
   * {@inheritDoc}.
   */
  protected function blockAccess(AccountInterface $account) {
    if ($account->hasPermission('access vardumper information')) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

  /**
   * {@inheritDoc}.
   */
  public function build() {
    $items = [];
    foreach ($this->session->getFlashBag()->get('vardumper', []) as $message) {
      $items[] = $message;
    }

    if ($items) {
      return [
        '#theme' => 'item_list',
        '#items' => $items,
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
  }

}
