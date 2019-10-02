<?php

namespace Drupal\context_breadcrumb\Breadcrumb;

use Drupal\context\ContextManager;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Class ConextBreadcrumbBuilder.
 */
class ContextBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * The context breadcrumb reactions.
   *
   * @var \Drupal\context\ContextReactionInterface
   */
  protected $contextReactions;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\Node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\User\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\Taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The token.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The context manager.
   *
   * @var \Drupal\context\ContextManager
   */
  protected $contextManager;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs the ConextBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   The entity manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   * @param \Drupal\Core\Utility\Token $token
   *   The token.
   * @param \Drupal\context\ContextManager $contextManager
   *   The context manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entityManager, AccountInterface $account, Token $token, ContextManager $contextManager, LoggerChannelFactoryInterface $logger) {
    $this->entityManager = $entityManager;
    $this->nodeStorage = $entityManager->getStorage('node');
    $this->userStorage = $entityManager->getStorage('user');
    $this->termStorage = $entityManager->getStorage('taxonomy_term');
    $this->user = $account;
    $this->token = $token;
    $this->contextManager = $contextManager;
    $this->logger = $logger;
  }

  /**
   * Validate string is token.
   *
   * @param string $str
   *   The string to validate token.
   *
   * @return bool
   *   Validate result.
   */
  public static function isToken($str) {
    return strpos($str, '[') !== FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $this->contextReactions = $this->contextManager->getActiveReactions('context_breadcrumb');
    return !empty($this->contextReactions);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    if (!empty($this->contextReactions)) {
      /** @var \Drupal\context\ContextReactionInterface $reaction */
      $reaction = $this->contextReactions[0];
      $contextBreadcrumbs = $reaction->execute();
      foreach ($contextBreadcrumbs as $contextBreadcrumb) {
        try {
          if (!empty($contextBreadcrumb['title'])) {
            if ($contextBreadcrumb['url'] == '<front>') {
              $contextBreadcrumb['url'] = '/';
            }

            if (!empty($contextBreadcrumb['token'])) {
              $token_data = [];
              $params = $route_match->getParameters();
              foreach ($params->keys() as $key) {
                $param_object = $params->get($key);
                if ($key == 'node' && !is_object($param_object)) {
                  $param_object = Node::load($param_object);
                }
                if ($key == 'user' && !is_object($param_object)) {
                  $param_object = User::load($param_object);
                }
                if ($key == 'node_revision' && !is_object($param_object)) {
                  $param_object = \Drupal::entityTypeManager()
                    ->getStorage('node')
                    ->loadRevision($param_object);
                }
                $token_data[$key] = $param_object;
              }
              if (self::isToken($contextBreadcrumb['title'])) {
                $contextBreadcrumb['title'] = $this->token->replace($contextBreadcrumb['title'], $token_data);
              }
              if (self::isToken($contextBreadcrumb['url'])) {
                $replace_url = $this->token->replace($contextBreadcrumb['url'], $token_data);
                $contextBreadcrumb['url'] = $replace_url === $contextBreadcrumb['url'] ? 'internal:/' : $replace_url;
                $breadcrumb->addLink(Link::fromTextAndUrl($this->t($contextBreadcrumb['title']), Url::fromUri($contextBreadcrumb['url'])));
              }
              else {
                $url = $contextBreadcrumb['url'] === '<nolink>' ? Url::fromRoute($contextBreadcrumb['url']) : Url::fromUserInput($contextBreadcrumb['url']);
                $breadcrumb->addLink(Link::fromTextAndUrl($this->t($contextBreadcrumb['title']), $url));
              }
            }
            elseif (strpos($contextBreadcrumb['url'], 'http://') !== FALSE || strpos($contextBreadcrumb['url'], 'https://') !== FALSE) {
                // External Uri.
                $breadcrumb->addLink(Link::fromTextAndUrl($this->t($contextBreadcrumb['title']), Url::fromUri($contextBreadcrumb['url'])));
            }
            else {
              $url = $contextBreadcrumb['url'] === '<nolink>' ? Url::fromRoute($contextBreadcrumb['url']) : Url::fromUserInput($contextBreadcrumb['url']);
              $breadcrumb->addLink(Link::fromTextAndUrl($this->t($contextBreadcrumb['title']), $url));
            }
          }
        } catch (\Exception $e) {
          $this->logger->get('context_breadcrumb')->error($e->getMessage());
        }
      }
    }

    $params = $route_match->getParameters()->all();
    foreach ($params as $param) {
      if ($param instanceof CacheableDependencyInterface) {
        $breadcrumb->addCacheableDependency($param);
      }
    }
    $breadcrumb->addCacheContexts(['url']);
    $breadcrumb->addCacheTags(['context:breadcrumb']);
    return $breadcrumb;
  }

}
