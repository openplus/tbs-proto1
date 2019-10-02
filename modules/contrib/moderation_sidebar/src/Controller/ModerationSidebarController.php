<?php

namespace Drupal\moderation_sidebar\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\content_moderation\ModerationInformation;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\LocalTaskManager;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\moderation_sidebar\Form\QuickTransitionForm;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Endpoints for the Moderation Sidebar module.
 */
class ModerationSidebarController extends ControllerBase {

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInformation;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The local task manager.
   *
   * @var \Drupal\Core\Menu\LocalTaskManagerInterface
   */
  protected $localTaskManager;

  /**
   * Creates a ModerationSidebarController instance.
   *
   * @param \Drupal\content_moderation\ModerationInformation $moderation_information
   *   The moderation information service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $local_task_manager
   *   The local task manager.
   */
  public function __construct(ModerationInformation $moderation_information, RequestStack $request_stack, DateFormatterInterface $date_formatter, ModuleHandlerInterface $module_handler, LocalTaskManagerInterface $local_task_manager) {
    $this->moderationInformation = $moderation_information;
    $this->request = $request_stack->getCurrentRequest();
    $this->dateFormatter = $date_formatter;
    $this->moduleHandler = $module_handler;
    $this->localTaskManager = $local_task_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $moderation_info = $container->get('content_moderation.moderation_information');

    // We need an instance of LocalTaskManager that thinks we're viewing the
    // entity. To accomplish this, we need to mock a request stack with a fake
    // request. This looks crazy, but there is no other way to render
    // Local Tasks for an arbitrary path without this.
    /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    $request_stack = $container->get('request_stack');

    $attributes = $request_stack->getCurrentRequest()->attributes;
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $attributes->has('node') ? $attributes->get('node') : $attributes->get('entity');
    $fake_request_stack = new RequestStack();
    $current_request = $container->get('request_stack')->getCurrentRequest();
    $request = Request::create($entity->toUrl()->toString(), 'GET', [], [], [], $current_request->server->all(), NULL);

    /** @var \Drupal\Core\Routing\AccessAwareRouter $router */
    $router = $container->get('router');
    $router->matchRequest($request);
    $fake_request_stack->push($request);
    $route_match = new CurrentRouteMatch($fake_request_stack);

    $local_task_manager = new LocalTaskManager(
      $container->get('controller_resolver'),
      $fake_request_stack,
      $route_match,
      $container->get('router.route_provider'),
      $container->get('module_handler'),
      $container->get('cache.discovery'),
      $container->get('language_manager'),
      $container->get('access_manager'),
      $container->get('current_user')
    );

    return new static(
      $moderation_info,
      $request_stack,
      $container->get('date.formatter'),
      $container->get('module_handler'),
      $local_task_manager
    );
  }

  /**
   * Displays information relevant to moderating an entity in-line.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A moderated entity.
   *
   * @return array
   *   The render array for the sidebar.
   */
  public function sideBar(ContentEntityInterface $entity) {
    // Load the correct translation.
    $language = $this->languageManager()->getCurrentLanguage();
    $entity = $entity->getTranslation($language->getId());
    $entity_type_id = $entity->getEntityTypeId();

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['moderation-sidebar-container'],
      ],
    ];

    // Add information about this Entity to the top of the bar.
    if ($this->moderationInformation->isModeratedEntity($entity)) {
      $state = $this->getModerationState($entity);
      $state_label = $state->label();
    }
    elseif ($entity->hasField('status')) {
      $state_label = $entity->get('status') ? $this->t('Published') : $this->t('Unpublished');
    }
    else {
      $state_label = $this->t('Published');
    }

    $build['info'] = [
      '#theme' => 'moderation_sidebar_info',
      '#state' => $state_label,
    ];

    if ($entity instanceof RevisionLogInterface) {
      $user = $entity->getRevisionUser();
      $time = (int) $entity->getRevisionCreationTime();
      $time_pretty = $this->getPrettyTime($time);
      $build['info']['#revision_author'] = $user->getDisplayName();
      $build['info']['#revision_author_link'] = $user->toLink()->toRenderable();
      $build['info']['#revision_time'] = $time;
      $build['info']['#revision_time_pretty'] = $time_pretty;
    }

    $build['actions'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['moderation-sidebar-actions'],
      ],
    ];

    if ($this->moderationInformation->isModeratedEntity($entity)) {
      $is_latest = $this->moderationInformation->isLatestRevision($entity);

      // Provide a link to the latest entity.
      if (!$is_latest) {
        $build['actions']['view_latest'] = [
          '#title' => $this->t('View existing draft'),
          '#type' => 'link',
          '#url' => Url::fromRoute("entity.{$entity_type_id}.latest_version", [
            $entity_type_id => $entity->id(),
          ]),
          '#attributes' => [
            'class' => ['moderation-sidebar-link', 'button'],
          ],
        ];
      }

      // Provide a link to the default display of the entity.
      if (!$entity->isDefaultRevision()) {
        $build['actions']['view_default'] = [
          '#title' => $this->t('View live content'),
          '#type' => 'link',
          '#url' => $entity->toLink()->getUrl(),
          '#attributes' => [
            'class' => ['moderation-sidebar-link', 'button'],
          ],
        ];
      }

      // Show an edit link if this is the latest revision.
      if ($is_latest && $entity->access('update')) {
        $build['actions']['edit'] = [
          '#title' => $this->moderationInformation->isLiveRevision($entity) ? $this->t('Edit content') : $this->t('Edit draft'),
          '#type' => 'link',
          '#url' => $entity->toLink(NULL, 'edit-form')->getUrl(),
          '#attributes' => [
            'class' => ['moderation-sidebar-link', 'button'],
          ],
        ];
      }

      // Only show the entity delete action on the default revision.
      if ($entity->isDefaultRevision() && $entity->access('delete')) {
        $build['actions']['delete'] = [
          '#title' => $this->t('Delete content'),
          '#type' => 'link',
          '#url' => $entity->toLink(NULL, 'delete-form')->getUrl(),
          '#attributes' => [
            'class' => ['moderation-sidebar-link', 'button', 'button--danger'],
          ],
          '#weight' => 1,
        ];
      }

      // We maintain our own inline revisions tab.
      if ($entity_type_id === 'node' && \Drupal::service('access_check.node.revision')->checkAccess($entity, \Drupal::currentUser()->getAccount())) {
        $build['actions']['version_history'] = [
          '#title' => $this->t('Show revisions'),
          '#type' => 'link',
          '#url' => Url::fromRoute('moderation_sidebar.node.version_history', [
            'node' => $entity->id(),
          ], ['query' => ['latest' => $is_latest]]),
          '#attributes' => [
            'class' => ['moderation-sidebar-link', 'button', 'use-ajax'],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
          ],
        ];
      }

      // We maintain our own inline translate tab.
      if ($this->moduleHandler()->moduleExists('content_translation') && \Drupal::service('content_translation.manager')->isSupported($entity_type_id)) {
        $build['actions']['translate'] = [
          '#title' => $this->t('Translate'),
          '#type' => 'link',
          '#url' => Url::fromRoute('moderation_sidebar.translate', [
            'entity_type' => $entity_type_id,
            'entity' => $entity->id(),
          ], ['query' => ['latest' => $is_latest]]),
          '#attributes' => [
            'class' => ['moderation-sidebar-link', 'button', 'use-ajax'],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
          ],
        ];
      }

      // Provide a list of actions representing transitions for this revision.
      if ($is_latest) {
        $build['actions']['quick_draft_form'] = $this->formBuilder()->getForm(QuickTransitionForm::class, $entity);
        $build['actions']['quick_draft_form']['#weight'] = 2;
      }
    }

    // Add a list of (non duplicated) local tasks.
    $build['actions'] += $this->getLocalTasks($entity);

    // Allow other module to alter our build.
    $this->moduleHandler->alter('moderation_sidebar', $build, $entity);

    return $build;
  }

  /**
   * Displays the moderation sidebar for the latest revision of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A moderated entity.
   *
   * @return array
   *   The render array for the sidebar.
   */
  public function sideBarLatest(ContentEntityInterface $entity) {
    $entity = $this->moderationInformation->getLatestRevision($entity->getEntityTypeId(), $entity->id());
    return $this->sideBar($entity);
  }

  /**
   * Renders the sidebar title for moderating this Entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A moderated entity.
   *
   * @return string
   *   The title of the sidebar.
   */
  public function title(ContentEntityInterface $entity) {
    return $entity->label();
  }

  /**
   * Generates an simple list of revisions for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(NodeInterface $node) {
    $langcode = $node->language()->getId();
    $node_storage = $this->entityTypeManager()->getStorage('node');

    $result = $node_storage->getQuery()
      ->allRevisions()
      ->condition($node->getEntityType()->getKey('id'), $node->id())
      ->sort($node->getEntityType()->getKey('revision'), 'DESC')
      ->execute();

    $build = $this->getBackButton($node);

    $count = 0;
    foreach (array_keys($result) as $vid) {
      if ($count >= 5) {
        break;
      }
      /** @var \Drupal\node\NodeInterface $revision */
      $revision = $node_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $user = $revision->getRevisionUser();
        $message = !empty($revision->revision_log->value) ? $revision->revision_log->value : $this->t('No message');
        // Use revision link to link to revisions that are not active.
        $time = $revision->revision_timestamp->value;
        $pretty_time = $this->getPrettyTime($revision->revision_timestamp->value);
        if ($vid != $node->getRevisionId()) {
          $link = new Link($pretty_time, new Url('entity.node.revision', ['node' => $node->id(), 'node_revision' => $vid]));
        }
        else {
          $link = $node->toLink($pretty_time);
        }

        $build[] = [
          '#theme' => 'moderation_sidebar_revision',
          '#revision_message' => ['#markup' => $message, '#allowed_tags' => Xss::getHtmlTagList()],
          '#revision_time' => $time,
          '#revision_time_pretty' => $pretty_time,
          '#revision_author' => $user->getDisplayName(),
          '#revision_author_link' => $user->toLink()->toRenderable(),
          '#revision_link' => $link,
        ];
        ++$count;
      }
    }

    $build[] = [
      '#title' => $this->t('View all revisions'),
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.node.version_history', [
        'node' => $node->id(),
      ]),
      '#attributes' => [
        'class' => ['moderation-sidebar-link', 'button'],
      ],
    ];

    return $build;
  }

  /**
   * Generate a simple list of translations with quick-add buttons.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function translateOverview(ContentEntityInterface $entity) {
    $entity_type = $entity->getEntityType();
    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $account = $this->currentUser();

    if (!\Drupal::moduleHandler()->moduleExists('content_translation') || !\Drupal::service('content_translation.manager')->isSupported($entity_type_id)) {
      throw new AccessDeniedHttpException();
    }

    $build = $this->getBackButton($entity);

    $build[] = [
      '#markup' => t('<p>The current language is <strong>@language</strong></p>', [
        '@language' => $entity->language()->getName(),
      ]),
    ];

    $can_create = $account->hasPermission('translate any entity');
    if (!$can_create) {
      $granularity = $entity_type->getPermissionGranularity();
      $permission = $granularity === 'bundle' ? "translate $bundle $entity_type_id" : "translate $entity_type_id";
      $can_create = $account->hasPermission($permission);
    }

    $languages = $this->languageManager()->getLanguages();
    $translations = $entity->getTranslationLanguages();

    if ($this->languageManager()->isMultilingual()) {
      // Determine whether the current entity is translatable.
      $translatable = FALSE;
      foreach ($entity->getFieldDefinitions() as $instance) {
        if ($instance->isTranslatable()) {
          $translatable = TRUE;
          break;
        }
      }

      foreach ($languages as $language) {
        $langcode = $language->getId();
        if ($langcode === $entity->language()->getId()) {
          continue;
        }
        if (array_key_exists($langcode, $translations)) {
          $translation = $entity->getTranslation($langcode);
          $build[] = [
            '#title' => $this->t('View @language translation', [
              '@language' => $language->getName(),
            ]),
            '#type' => 'link',
            '#url' => $translation->toUrl(),
            '#attributes' => [
              'class' => ['moderation-sidebar-link', 'button'],
            ],
          ];
        }
        elseif ($can_create && $translatable) {
          $build[] = [
            '#title' => $this->t('Create @language translation', [
              '@language' => $language->getName(),
            ]),
            '#type' => 'link',
            '#url' => Url::fromRoute(
              "entity.$entity_type_id.content_translation_add",
              [
                'source' => $entity->getUntranslated()->language()->getId(),
                'target' => $language->getId(),
                $entity_type_id => $entity->id(),
              ],
              [
                'language' => $language,
              ]
            ),
            '#attributes' => [
              'class' => ['moderation-sidebar-link', 'button'],
            ],
          ];
        }
      }
    }

    return $build;
  }

  /**
   * Gets the Moderation State of a given Entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity.
   *
   * @return \Drupal\workflows\StateInterface
   *   The moderation state for the given entity.
   */
  protected function getModerationState(ContentEntityInterface $entity) {
    $state_id = $entity->moderation_state->get(0)->getValue()['value'];
    $workflow = $this->moderationInformation->getWorkFlowForEntity($entity);
    return $workflow->getTypePlugin()->getState($state_id);
  }

  /**
   * Gathers a list of non-duplicated tasks, themed like our other buttons.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity.
   *
   * @return array
   *   A render array representing local tasks for this entity.
   */
  protected function getLocalTasks(ContentEntityInterface $entity) {
    $tasks = $this->localTaskManager->getLocalTasks("entity.{$entity->getEntityTypeId()}.canonical", 0);
    $tabs = [];
    if (isset($tasks['tabs']) && !empty($tasks['tabs'])) {
      foreach ($tasks['tabs'] as $name => $tab) {
        // If this is a moderated node, we provide buttons for certain actions.
        $duplicated_tab = preg_match('/^.*(canonical|edit_form|delete_form|latest_version_tab|entity\.node\.version_history|content_translation_overview)$/', $name);
        if (!$this->moderationInformation->isModeratedEntity($entity) || !$duplicated_tab) {
          $attributes = [];
          if (isset($tab['#link']['localized_options']['attributes'])) {
            $attributes = $tab['#link']['localized_options']['attributes'];
          }
          $attributes['class'][] = 'moderation-sidebar-link';
          $attributes['class'][] = 'button';
          $tabs[$name] = [
            '#title' => $tab['#link']['title'],
            '#type' => 'link',
            '#url' => $tab['#link']['url'],
            '#attributes' => $attributes,
          ];
        }
      }
    }
    return $tabs;
  }

  /**
   * Formats a timestamp to be presentable to end users.
   *
   * @param int $time
   *   The revision timestamp.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Markup representing a presentable time.
   */
  protected function getPrettyTime($time) {
    $too_old = strtotime('-1 month');
    // Show formatted time differences for edits younger than a month.
    if ($time > $too_old) {
      $diff = $this->dateFormatter->formatTimeDiffSince($time, ['granularity' => 1]);
      $time_pretty = $this->t('@diff ago', ['@diff' => $diff]);
    }
    else {
      $date = date('m/d/Y - h:i A', $time);
      $time_pretty = $this->t('on @date', ['@date' => $date]);
    }
    return $time_pretty;
  }

  /**
   * Generates the render array for an AJAX-enabled back button.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity.
   *
   * @return array
   *   A render array representing a back button.
   */
  protected function getBackButton(ContentEntityInterface $entity) {
    $params = [
      'entity' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ];

    if (\Drupal::request()->get('latest')) {
      $back_url = Url::fromRoute('moderation_sidebar.sidebar_latest', $params);
    }
    else {
      $back_url = Url::fromRoute('moderation_sidebar.sidebar', $params);
    }

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['moderation-sidebar-container']],
      [
        '#title' => $this->t('← Back'),
        '#type' => 'link',
        '#url' => $back_url,
        '#attributes' => [
          'class' => ['use-ajax', 'moderation-sidebar-back-button'],
          'data-dialog-type' => 'dialog',
          'data-dialog-renderer' => 'off_canvas',
        ],
      ],
    ];

    return $build;
  }

}
