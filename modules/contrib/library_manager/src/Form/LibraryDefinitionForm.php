<?php

namespace Drupal\library_manager\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Library definition form.
 *
 * @property \Drupal\library_manager\LibraryDefinitionInterface $entity
 */
class LibraryDefinitionForm extends EntityForm {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The library discovery service.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * Constructs the form object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   Condition manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConditionManager $condition_manager) {
    $this->configFactory = $config_factory;
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.condition')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $settings = $this->configFactory->get('library_manager.settings')->get();

    $form = parent::form($form, $form_state);

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\library_manager\Entity\LibraryDefinition::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['mode'] = [
      '#type' => 'radios',
      '#options' => [
        'new' => $this->t('Register new library'),
        'override' => $this->t('Override existing library'),
      ],
      '#default_value' => $this->entity->get('target') ? 'override' : 'new',
    ];

    $form['target'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Library to override'),
      '#default_value' => $this->entity->get('target'),
      '#autocomplete_route_name' => 'library_manager.library_autocomplete',
      '#states' => [
        'visible' => [
          ':input[name="mode"]' => ['value' => 'override'],
        ],
      ],
    ];

    $form['remote'] = [
      '#type' => 'url',
      '#title' => $this->t('Remote'),
      '#default_value' => $this->entity->get('remote'),
    ];

    $form['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Version'),
      '#required' => TRUE,
      '#default_value' => $this->entity->get('version'),
    ];

    $license = $this->entity->get('license');
    $form['license']['#tree'] = TRUE;
    $form['license'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('License'),
      '#tree' => TRUE,
    ];
    $data_list = new FormattableMarkup('<datalist id="license"><option value="GNU-GPL-2.0-or-later"><option value="MIT"><option value="Public Domain"></datalist>', []);
    $form['license']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#field_suffix' => $data_list,
      '#attributes' => ['list' => ['license']],
      '#default_value' => $license['name'],
    ];
    $form['license']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#default_value' => $license['url'],
    ];
    $form['license']['gpl-compatible'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('GPL compatible'),
      '#default_value' => $license['gpl-compatible'],
    ];

    // JS files.
    $form['js'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('JS files'),
    ];

    $js_header = [
      $this->t('Name'),
      $this->t('Size'),
      $this->t('Type'),
      $this->t('Operations'),
    ];

    $form['js']['table'] = [
      '#type' => 'table',
      '#header' => $js_header,
      '#rows' => [],
      '#empty' => $this->t('JS files are not configured yet.'),
    ];

    $js = $this->entity->get('js');
    foreach ($js as $file_id => $js_data) {

      if ($js_data['external']) {
        if ($js_data['url']) {
          $file_url = Url::fromUri($js_data['url'], ['attributes' => ['target' => '_blank']]);
          $file_name = Link::fromTextAndUrl($js_data['file_name'], $file_url)->toString();
        }
        else {
          $file_name = $js_data['file_name'];
        }
        $rows = [
          $file_name,
          '',
          $this->t('External'),
        ];
      }
      else {
        $file_path = '/' . $settings['libraries_path'] . '/' . $this->entity->id() . '/' . $js_data['file_name'];
        $file_url = Url::fromUri('internal:' . $file_path, ['attributes' => ['target' => '_blank']]);
        $file_name = Link::fromTextAndUrl($js_data['file_name'], $file_url)->toString();
        $rows = [
          $file_name,
          format_size(mb_strlen($js_data['code'])),
          $this->t('Local'),
        ];
      }

      $route_parameters = [
        'library_definition' => $this->entity->id(),
        'file_id' => $file_id,
      ];

      $operation_links = [
        'edit' => [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('entity.library_definition.edit_js_form', $route_parameters),
        ],
        'delete' => [
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('entity.library_definition.delete_js_form', $route_parameters),
        ],
      ];

      $operation_data = [
        '#type' => 'operations',
        '#links' => $operation_links,
      ];
      $form['js']['table']['#rows'][$file_id] = $rows + ['operations' => ['data' => $operation_data]];
    }

    // Do not show Add JS file button until the entity is persisted.
    if (!$this->entity->isNew()) {
      $form['js']['add_js'] = [
        '#type' => 'link',
        '#title' => $this->t('Add JS file'),
        '#url' => new Url('entity.library_definition.add_js_form', ['library_definition' => $this->entity->id()]),
        '#attributes' => ['class' => ['button', 'button--small']],
      ];
    }

    // CSS files.
    $form['css'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('CSS files'),
    ];

    $css_header = [
      $this->t('Name'),
      $this->t('Size'),
      $this->t('Type'),
      $this->t('Operations'),
    ];

    $form['css']['table'] = [
      '#type' => 'table',
      '#header' => $css_header,
      '#rows' => [],
      '#empty' => $this->t('CSS files are not configured yet.'),
    ];

    $css = $this->entity->get('css');
    foreach ($css as $file_id => $css_data) {

      if ($css_data['external']) {
        if ($css_data['url']) {
          $file_url = Url::fromUri($css_data['url'], ['attributes' => ['target' => '_blank']]);
          $file_name = Link::fromTextAndUrl($css_data['file_name'], $file_url)->toString();
        }
        else {
          $file_name = $css_data['file_name'];
        }
        $rows = [
          $file_name,
          '',
          $this->t('External'),
        ];
      }
      else {
        $file_path = '/' . $settings['libraries_path'] . '/' . $this->entity->id() . '/' . $css_data['file_name'];
        $file_url = Url::fromUri('internal:' . $file_path, ['attributes' => ['target' => '_blank']]);
        $file_name = Link::fromTextAndUrl($css_data['file_name'], $file_url)->toString();
        $rows = [
          $file_name,
          format_size(mb_strlen($css_data['code'])),
          $this->t('Local'),
        ];
      }

      $route_parameters = [
        'library_definition' => $this->entity->id(),
        'file_id' => $file_id,
      ];

      $operation_links = [
        'edit' => [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('entity.library_definition.edit_css_form', $route_parameters),
        ],
        'delete' => [
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('entity.library_definition.delete_css_form', $route_parameters),
        ],
      ];

      $operation_data = [
        '#type' => 'operations',
        '#links' => $operation_links,
      ];

      $form['css']['table']['#rows'][$file_id] = $rows + ['operations' => ['data' => $operation_data]];
    }

    if (!$this->entity->isNew()) {
      $form['css']['add_css'] = [
        '#type' => 'link',
        '#title' => $this->t('Add CSS file'),
        '#url' => new Url('entity.library_definition.add_css_form', ['library_definition' => $this->entity->id()]),
        '#attributes' => ['class' => ['button', 'button--small']],
      ];

    }

    // Dependencies.
    $form['dependencies'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dependencies'),
    ];

    $form['dependencies']['wrapper'] = [
      '#type' => 'container',
      '#id' => 'wrapper',
    ];

    $form['dependencies']['wrapper']['library_dependencies']['#tree'] = TRUE;

    $stored_dependencies = $this->entity->get('library_dependencies');
    $submitted_dependencies = (array) $form_state->getValue('library_dependencies');

    $max_delta = max(count($stored_dependencies), count($submitted_dependencies) + 1);

    for ($delta = 0; $delta < $max_delta; $delta++) {
      $form['dependencies']['wrapper']['library_dependencies'][$delta] = [
        '#type' => 'textfield',
        '#autocomplete_route_name' => 'library_manager.library_autocomplete',
        '#default_value' => isset($stored_dependencies[$delta]) ? $stored_dependencies[$delta] : '',
      ];
    }

    $form['dependencies']['add'] = [
      '#type' => 'button',
      '#name' => 'add_library',
      '#value' => $this->t('Add dependency'),
      '#submit' => [[$this, 'addMoreSubmit']],
      '#attributes' => ['class' => ['button', 'button--small']],
      '#ajax' => [
        'callback' => [$this, 'addMoreCallback'],
        'wrapper' => 'wrapper',
      ],
    ];

    $form['load'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Load the library automatically according to visibility rules'),
      '#default_value' => $this->entity->get('load'),
    ];

    $form['visibility'] = $this->buildVisibilityInterface([], $form_state);

    return $form;
  }

  /**
   * Dependencies ajax callback.
   */
  public function addMoreCallback(array $form, FormStateInterface $form_state) {
    return $form['dependencies']['wrapper'];
  }

  /**
   * Dependencies submit callback.
   */
  public function addMoreSubmit(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $message = $this->entity->save() == SAVED_NEW ?
      $this->t('Library definition has been created.') : $this->t('Library definition has been updated.');
    $this->messenger()->addStatus($message);

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
  }

  /**
   * Helper function for building the visibility UI form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form array with the visibility UI added in.
   */
  protected function buildVisibilityInterface(array $form, FormStateInterface $form_state) {

    $form['#tree'] = TRUE;

    $form['visibility_tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Visibility'),
      '#parents' => ['visibility_tabs'],
    ];

    $visibility = $this->entity->getVisibility();

    foreach ($this->conditionManager->getDefinitions() as $condition_id => $definition) {

      /** @var \Drupal\Core\Condition\ConditionInterface $condition */
      $condition = $this->conditionManager->createInstance($condition_id, isset($visibility[$condition_id]) ? $visibility[$condition_id] : []);
      $form_state->set(['conditions', $condition_id], $condition);
      $condition_form = $condition->buildConfigurationForm([], $form_state);
      $condition_form['#type'] = 'details';
      $condition_form['#title'] = $condition->getPluginDefinition()['label'];
      $condition_form['#group'] = 'visibility_tabs';
      $form[$condition_id] = $condition_form;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $this->validateVisibility($form, $form_state);
  }

  /**
   * Helper function to independently validate the visibility UI.
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function validateVisibility(array $form, FormStateInterface $form_state) {

    // Validate visibility condition settings.
    foreach ($form_state->getValue('visibility') as $condition_id => $values) {
      // All condition plugins use 'negate' as a Boolean in their schema.
      // However, certain form elements may return it as 0/1. Cast here to
      // ensure the data is in the expected type.
      if (array_key_exists('negate', $values)) {
        $values['negate'] = (bool) $values['negate'];
      }

      // Allow the condition to validate the form.
      $condition = $form_state->get(['conditions', $condition_id]);
      $condition_values = (new FormState())
        ->setValues($values);
      $condition->validateConfigurationForm($form, $condition_values);
      // Update the original form values.
      $form_state->setValue(['visibility', $condition_id], $condition_values->getValues());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $entity = $this->entity;

    if ($form_state->getValue('mode') == 'new') {
      $entity->set('target', NULL);
    }

    $dependencies = array_values(array_filter($form_state->getValue('library_dependencies')));
    $entity->set('library_dependencies', $dependencies);

    // Submit visibility condition settings.
    foreach ($form_state->getValue('visibility') as $condition_id => $values) {
      // Allow the condition to submit the form.
      $condition = $form_state->get(['conditions', $condition_id]);
      $condition_values = (new FormState())
        ->setValues($values);
      $condition->submitConfigurationForm($form, $condition_values);

      if ($condition instanceof ContextAwarePluginInterface) {
        $context_mapping = isset($values['context_mapping']) ? $values['context_mapping'] : [];
        $condition->setContextMapping($context_mapping);
      }
      // Update the original form values.
      $condition_configuration = $condition->getConfiguration();
      $form_state->setValue(['visibility', $condition_id], $condition_configuration);
      // Update the visibility conditions.
      $entity->getVisibilityConditions();
      $entity->getVisibilityConditions()->addInstanceId($condition_id, $condition_configuration);
    }

  }

}
