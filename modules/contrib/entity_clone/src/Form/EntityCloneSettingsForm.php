<?php

namespace Drupal\entity_clone\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_clone\EntityCloneSettingsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide the settings form for entity clone.
 */
class EntityCloneSettingsForm extends ConfigFormBase implements ContainerInjectionInterface {

  /**
   * The entity clone settings manager.
   *
   * @var \Drupal\entity_clone\EntityCloneSettingsManager
   */
  protected $entityCloneSettingsManager;

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\entity_clone\EntityCloneSettingsManager $entity_clone_settings_manager
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityCloneSettingsManager $entity_clone_settings_manager) {
    parent::__construct($config_factory);
    $this->entityCloneSettingsManager = $entity_clone_settings_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_clone.settings.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['entity_clone.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_clone_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;

    $form['form_settings'] = [
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#title' => $this->t('Clone form settings'),
      '#description' => $this->t("
        For each type of child entity (the entity that's referenced by the entity being
        cloned), please set your cloning preferences. This will affect the clone form presented to users when they
        clone entities. Default behaviour for whether or not the child entities should be cloned is specified in
        the left-most column.  To prevent users from altering behaviour for each type when they're actually cloning
        (but still allowing them to see what will happen), use the middle column. The right-most column can be used
        to hide the form options from users completely. This will run the clone operation with the defaults set here
        (in the left-most column). See the clone form (by cloning an entity) for more information.
      "),
      '#open' => TRUE,
      '#collapsible' => FALSE,
    ];

    $form['form_settings']['table'] = [
      '#type' => 'table',
      '#header' => [
        'label' => $this->t('Label'),
        'default_value' => $this->t('Checkboxes default value'),
        'disable'  => $this->t('Disable checkboxes'),
        'hidden' => $this->t('Hide checkboxes'),
      ],
    ];

    foreach ($this->entityCloneSettingsManager->getContentEntityTypes() as $type_id => $type) {
      $form['form_settings']['table'][$type_id] = [
        'label' => [
          '#type' => 'label',
          '#title' => $this->t('@type', [
            '@type' => $type->getLabel(),
          ]),
        ],
        'default_value' => [
          '#type' => 'checkbox',
          '#default_value' => $this->entityCloneSettingsManager->getDefaultValue($type_id),
        ],
        'disable' => [
          '#type' => 'checkbox',
          '#default_value' => $this->entityCloneSettingsManager->getDisableValue($type_id),
        ],
        'hidden' => [
          '#type' => 'checkbox',
          '#default_value' => $this->entityCloneSettingsManager->getHiddenValue($type_id),
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entityCloneSettingsManager->setFormSettings($form_state->getValue('form_settings'));
    parent::submitForm($form, $form_state);
  }

}
