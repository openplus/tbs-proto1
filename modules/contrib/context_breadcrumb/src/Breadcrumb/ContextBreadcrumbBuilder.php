<?php

namespace Drupal\context_breadcrumb\Breadcrumb;

use Drupal\context\ContextManager;
use Drupal\context_breadcrumb\Plugin\ContextReaction\Breadcrumb as ContextBreadcrumb;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
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
    return strpos($str, '[') !== FALSE || strpos($str, '{{') !== FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $this->contextReactions = $this->contextManager->getActiveReactions('context_breadcrumb');
    return !empty($this->contextReactions);
  }

  /**
   * Translates a string to the current language or to a given language.
   *
   * See \Drupal\Core\StringTranslation\TranslatableMarkup::__construct() for
   * important security information and usage guidelines.
   *
   * In order for strings to be localized, make them available in one of the
   * ways supported by the
   *
   * @link https://www.drupal.org/node/322729 Localization API @endlink. When
   * possible, use the \Drupal\Core\StringTranslation\StringTranslationTrait
   * $this->t(). Otherwise create a new
   * \Drupal\Core\StringTranslation\TranslatableMarkup object.
   *
   * @param string $string
   *   A string containing the English text to translate.
   * @param array $args
   *   (optional) An associative array of replacements to make after
   *   translation. Based on the first character of the key, the value is
   *   escaped and/or themed. See
   *   \Drupal\Component\Render\FormattableMarkup::placeholderFormat() for
   *   details.
   * @param array $options
   *   (optional) An associative array of additional options, with the following
   *   elements:
   *   - 'langcode' (defaults to the current language): A language code, to
   *     translate to a language other than what is used to display the page.
   *   - 'context' (defaults to the empty context): The context the source
   *     string belongs to. See the
   *
   * @link i18n Internationalization topic @endlink for more information
   *     about string contexts.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   An object that, when cast to a string, returns the translated string.
   *
   * @see \Drupal\Component\Render\FormattableMarkup::placeholderFormat()
   * @see \Drupal\Core\StringTranslation\TranslatableMarkup::__construct()
   *
   * @ingroup sanitization
   */
  protected function trans($string, array $args = [], array $options = []) {
    return new TranslatableMarkup($string, $args, $options, $this->getStringTranslation());
  }

  /**
   * Render data.
   *
   * @param string $title
   *   The title.
   * @param string|int $renderType
   *   The render type.
   * @param array|mixed $data
   *   Context data.
   *
   * @return mixed|string|null
   *   Title render output.
   */
  protected function renderData($title, $renderType, $data) {
    if (strpos($title, '[') !== FALSE && strpos($title, ']') !== FALSE) {
      // Render token.
      return $this->token->replace($title, $data);
    }
    if (strpos($title, '{{') !== FALSE && strpos($title, '}}') !== FALSE) {
      $render_array = [
        '#type' => 'inline_template',
        '#template' => $title,
        '#context' => $data,
      ];
      $renderer = \Drupal::service('renderer');
      return (string) $renderer->render($render_array);
    }
    return $title;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    if (!empty($this->contextReactions)) {
      $language = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);
      foreach ($this->contextReactions as $reaction) {
        if (empty($reaction) || !($reaction instanceof ContextBreadcrumb)) {
          continue;
        }
        /** @var \Drupal\context\ContextReactionInterface $reaction */
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
                $contextBreadcrumb['title'] = $this->renderData($contextBreadcrumb['title'], $contextBreadcrumb['token'], $token_data);
                if ($contextBreadcrumb['url'] === '<nolink>') {
                  $contextBreadcrumb['url'] = Url::fromRoute($contextBreadcrumb['url']);
                }
                else {
                  $contextBreadcrumb['url'] = $this->renderData($contextBreadcrumb['url'], $contextBreadcrumb['token'], $token_data);
                }

                if ($contextBreadcrumb['url'] instanceof Url) {
                  $breadcrumb->addLink(Link::fromTextAndUrl($this->trans($contextBreadcrumb['title']), $contextBreadcrumb['url']));
                }

                if (is_string($contextBreadcrumb['url'])) {
                    if (strpos($contextBreadcrumb['url'], '://') !== FALSE) {
                        $breadcrumb->addLink(Link::fromTextAndUrl($this->t($contextBreadcrumb['title']), Url::fromUri($contextBreadcrumb['url'])));
                    }
                    else {
                       $breadcrumb->addLink(Link::fromTextAndUrl($this->trans($contextBreadcrumb['title']), Url::fromUserInput($contextBreadcrumb['url'])));
                    }
                }
              }
              else {
                $url = $contextBreadcrumb['url'] === '<nolink>' ? Url::fromRoute($contextBreadcrumb['url']) : Url::fromUserInput($contextBreadcrumb['url'], ['language' => $language]);
                $breadcrumb->addLink(Link::fromTextAndUrl($this->trans($contextBreadcrumb['title']), $url));
              }
            }
          }
          catch (\Exception $e) {
            $this->logger->get('context_breadcrumb')->error($e->getMessage());
          }
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
