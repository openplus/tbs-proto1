<?php

namespace Drupal\wxt_library\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'UserAccountBlock' block.
 *
 * @Block(
 *  id = "user_account_block",
 *  admin_label = @Translation("User Account"),
 * )
 */
class UserAccountBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The Account Proxy.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        AccountProxy $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $account_name = $this->currentUser->getAccountName();
    $roles = $this->currentUser->getRoles();
    $build = [];

    $build['user_account_block']['#markup'] = '<section id="wb-so">';
    $build['user_account_block']['#markup'] .= '<h2 class="wb-inv">' . $this->t('Sign-on information') . '</h2>';
    $build['user_account_block']['#markup'] .= '<div class="col-md-12 text-right">';

    if (!in_array("authenticated", $roles)) {
      $build['user_account_block']['#markup'] .= Link::fromTextAndUrl($this->t('Register'), Url::fromRoute('user.register', [], ['attributes' => ['class' => 'btn btn-default']]))->toString() . "\n";
      $build['user_account_block']['#markup'] .= Link::fromTextAndUrl($this->t('Sign in'), Url::fromRoute('user.login', [], ['attributes' => ['class' => 'btn btn-primary']]))->toString() . "\n";
    }
    else {
      $build['user_account_block']['#markup'] .= '<p class="btn">' . $this->t('Signed in as') . '<span class="wb-so-uname">' . ' ' . $account_name . '</span></p>';
      $build['user_account_block']['#markup'] .= Link::fromTextAndUrl($this->t('Account settings'), Url::fromRoute('user.page', [], ['attributes' => ['class' => 'btn btn-default']]))->toString() . "\n";
      $build['user_account_block']['#markup'] .= Link::fromTextAndUrl($this->t('Sign out'), Url::fromRoute('user.logout', [], ['attributes' => ['class' => 'btn btn-primary']]))->toString() . "\n";
    }

    $build['user_account_block']['#markup'] .= '</div></section>';

    // Ensure the block is cached per-user.
    $build['user_account_block']['#cache']['contexts'] = ['user'];

    return $build;
  }

}
