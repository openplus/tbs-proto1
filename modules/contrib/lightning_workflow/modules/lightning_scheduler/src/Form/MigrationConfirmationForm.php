<?php

namespace Drupal\lightning_scheduler\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\lightning_scheduler\Migrator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a UI for migrating or purging scheduled transition data.
 *
 * This class is final for the same reason that the Migrator service is: the
 * migration is not an API and should not be extended or re-used.
 */
final class MigrationConfirmationForm extends ConfirmFormBase {

  /**
   * The migrator service.
   *
   * @var \Drupal\lightning_scheduler\Migrator
   */
  protected $migrator;

  /**
   * MigrationConfirmationForm constructor.
   *
   * @param \Drupal\lightning_scheduler\Migrator $migrator
   *   The migrator service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   (optional) The messenger service.
   */
  public function __construct(Migrator $migrator, MessengerInterface $messenger = NULL) {
    $this->migrator = $migrator;

    if ($messenger) {
      $this->setMessenger($messenger);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lightning_scheduler.migrator'),
      $container->get('messenger')
    );
  }

  /**
   * Performs access check.
   *
   * @return AccessResult
   *   Allowed if the current user is droot (Drupal root).
   */
  public function access() {
    $uid = (int) $this->currentUser()->id();

    // This migration is serious business, so only droot can do it.
    return AccessResult::allowedIf($uid === 1);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_scheduler_migration_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity_types = $this->migrator->getEntityTypesToMigrate();

    if (empty($entity_types)) {
      $this->messenger()->addStatus($this->t('Hey, nice! All migrations are completed.'));
      return $form;
    }

    $form = parent::buildForm($form, $form_state);

    $form['purge'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#title' => $this->t('Purge without migrating'),
      '#description' => $this->t('Purging will allow you to discard existing scheduled transitions for a particular entity type without running the migration. This is useful if you don\'t have any scheduled transitions that you want to migrate. <strong>This will permanently delete scheduled transitions and cannot be undone.</strong>'),
      '#tree' => TRUE,
      'entity_type_id' => [
        '#type' => 'select',
        '#title' => $this->t('Entity type to purge'),
        '#options' => $this->migrator->entityTypeOptions($entity_types),
      ],
      'actions' => [
        '#type' => 'actions',
        'purge' => [
          '#type' => 'submit',
          '#value' => $this->t('Purge'),
          '#submit' => [
            '::purge',
          ],
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to migrate all scheduled transitions?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $entity_types = $this->migrator->getEntityTypesToMigrate();
    return $this->migrator->generatePreMigrationMessage($entity_types);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Continue');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('<front>');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $operations = [];

    $callback = [static::class, 'migrate'];

    foreach (array_keys($this->migrator->getEntityTypesToMigrate()) as $entity_type_id) {
      foreach ($this->migrator->query($entity_type_id)->execute() as $item) {
        $arguments = [$entity_type_id, $item];
        array_push($operations, [$callback, $arguments]);
      }

      array_push($operations, [
        [static::class, 'complete'],
        [$entity_type_id],
      ]);
    }
    batch_set(['operations' => $operations]);
  }

  /**
   * Batch API callback to migrate a single entity.
   */
  public static function migrate($entity_type_id, \stdClass $item) {
    \Drupal::service('lightning_scheduler.migrator')
      ->migrate($entity_type_id, $item);
  }

  /**
   * Batch API callback to mark an entity type's migration as completed.
   */
  public static function complete($entity_type_id) {
    \Drupal::service('lightning_scheduler.migrator')
      ->completeMigration($entity_type_id);
  }

  /**
   * Submit function to handle purging 1.x base field data.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function purge(array &$form, FormStateInterface $form_state) {
    $entity_type_id = $form_state->getValue(['purge', 'entity_type_id']);

    $this->migrator->purge($entity_type_id, 'scheduled_publication');
    $this->migrator->purge($entity_type_id, 'scheduled_moderation_state');
    $this->migrator->completeMigration($entity_type_id);

    $message = $this->t('Purged scheduled transitions for @entity_type.', [
      '@entity_type' => $form['purge']['entity_type_id']['#options'][$entity_type_id],
    ]);
    $this->messenger()->addStatus($message);
  }

}
