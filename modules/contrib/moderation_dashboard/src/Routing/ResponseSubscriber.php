<?php

namespace Drupal\moderation_dashboard\Routing;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to redirect user login to the Moderation Dashboard.
 */
class ResponseSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ResponseSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(AccountProxyInterface $current_user, ConditionManager $condition_manager, ConfigFactoryInterface $config_factory) {
    $this->currentUser = $current_user;
    $this->conditionManager = $condition_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Redirects user login to the Moderation Dashboard, when appropriate.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event.
   */
  public function onResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $request = $event->getRequest();

    $should_redirect = $this->configFactory
      ->get('moderation_dashboard.settings')
      ->get('redirect_on_login');

    if ($should_redirect && $response instanceof RedirectResponse) {
      $response_url_components = UrlHelper::parse($response->getTargetUrl());
      $has_destination = isset($response_url_components['query']['destination']);

      $is_login = $request->request->get('form_id') === 'user_login_form';
      $has_permission = $this->currentUser->hasPermission('use moderation dashboard');
      $has_moderated_content_type = $this->conditionManager->createInstance('has_moderated_content_type')->execute();

      if ($has_permission && $is_login && $has_moderated_content_type && !$has_destination) {
        $url = Url::fromRoute('page_manager.page_view_moderation_dashboard_moderation_dashboard-panels_variant-0', ['user' => $this->currentUser->id()]);
        $response->setTargetUrl($url->toString());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onResponse', 100];
    return $events;
  }

}
