<?php

namespace Drupal\lightning_workflow\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_roles\ContentRoleManager;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Update("2.3.0")
 */
final class Update230 implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  private $moduleInstaller;

  /**
   * The content role manager.
   *
   * @var \Drupal\lightning_roles\ContentRoleManager
   */
  private $contentRoleManager;

  /**
   * The workflow entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $workflowStorage;

  /**
   * Update230 constructor.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service.
   * @param EntityStorageInterface $workflow_storage
   *   The workflow entity storage handler.
   * @param TranslationInterface $translation
   *   (optional) The string translation service.
   * @param \Drupal\lightning_roles\ContentRoleManager
   *   (optional) The content role manager service.
   */
  public function __construct(ModuleInstallerInterface $module_installer, EntityStorageInterface $workflow_storage, TranslationInterface $translation = NULL, ContentRoleManager $content_role_manager = NULL) {
    $this->moduleInstaller = $module_installer;
    $this->workflowStorage = $workflow_storage;
    if ($translation) {
      $this->setStringTranslation($translation);
    }
    $this->contentRoleManager = $content_role_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $content_role_manager = NULL;

    if ($container->get('module_handler')->moduleExists('lightning_roles')) {
      $content_role_manager = $container->get('lightning.content_roles');
    }

    return new static(
      $container->get('module_installer'),
      $container->get('entity_type.manager')->getStorage('workflow'),
      $container->get('string_translation'),
      $content_role_manager
    );
  }

  /**
   * Enables the Moderation Sidebar module.
   *
   * @update
   *
   * @ask Do you want to enable the Moderation Sidebar module? This will also
   * install the Toolbar module and allow reviewers to use it.
   */
  public function enableModerationSidebar() {
    $this->moduleInstaller->install(['moderation_sidebar', 'toolbar']);

    if ($this->contentRoleManager) {
      $this->contentRoleManager
        ->grantPermissions('creator', [
          'use moderation sidebar',
        ])
        ->grantPermissions('reviewer', [
          'access toolbar',
          'use moderation sidebar',
        ]);
    }
  }

  /**
   * Alters editorial workflow transitions.
   *
   * @update
   */
  public function alterTransitions(StyleInterface $io) {
    /** @var \Drupal\workflows\WorkflowInterface $workflow */
    $workflow = $this->workflowStorage->load('editorial');
    if (empty($workflow)) {
      return;
    }

    $plugin = $workflow->getTypePlugin();
    $configuration = $plugin->getConfiguration();

    if ($plugin->hasTransitionFromStateToState('draft', 'review')) {
      $transition = $plugin->getTransitionFromStateToState('draft',  'review');

      $question = $this->t('Do you want to rename the "@draft_review" editorial workflow transition to "Send to review"?', [
        '@draft_review' => $transition->label(),
      ]);
      if ($io->confirm($question)) {
        $id = $transition->id();
        $configuration['transitions'][$id]['label'] = 'Send to review';
      }
    }

    if ($plugin->hasTransitionFromStateToState('archived', 'published')) {
      $transition = $plugin->getTransitionFromStateToState('archived', 'published');

      $question = $this->t('Do you want to rename the "@archived_published" editorial workflow transition to "Restore from archive"?', [
        '@archived_published' => $transition->label(),
      ]);
      if ($io->confirm($question)) {
        $id = $transition->id();
        $configuration['transitions'][$id]['label'] = 'Restore from archive';
      }
    }

    // Merge the archived_draft and create_new_draft transitions.
    if ($plugin->hasTransition('archived_draft') && $plugin->hasTransition('create_new_draft')) {
      $transition = $plugin->getTransition('create_new_draft');

      // If the create_new_draft transition can already handle archived items,
      // there's nothing to do. This is an edge case, but worth covering.
      if (array_key_exists('archived', $transition->from())) {
        return;
      }

      $question = $this->t('Do you want to allow the "@create_new_draft" editorial workflow transition to restore archived content into a draft state? This will remove the "@archived_draft" transition.', [
        '@create_new_draft' => $transition->label(),
        '@archived_draft' => $plugin->getTransition('archived_draft')->label(),
      ]);
      if ($io->confirm($question)) {
        unset($configuration['transitions']['archived_draft']);
        $configuration['transitions']['create_new_draft']['from'][] = 'archived';
      }
    }

    $workflow->set('type_settings', $configuration);
    $this->workflowStorage->save($workflow);
  }

}
