<?php

namespace Drupal\library_manager\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Library definition CSS form.
 *
 * @property \Drupal\library_manager\LibraryDefinitionInterface $entity
 */
class LibraryDefinitionCssForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $defaults = [
      'file_name' => '',
      'group' => 'component',
      'code' => '',
      'preprocess' => TRUE,
      'minified' => FALSE,
      'external' => FALSE,
      'url' => '',
      'weight' => 0,
    ];

    $route_match = $this->getRouteMatch();
    $file_id = $route_match->getParameter('file_id');
    if (!$route_match->getParameter('is_new')) {
      // The edited CSS file should exist in the entity.
      $data = $this->entity->getCssFile($file_id);
      if (!$data) {
        throw new NotFoundHttpException();
      }
      $defaults = $data + $defaults;
    }

    $form['file_id'] = [
      '#type' => 'value',
      '#value' => $file_id,
    ];

    $form['file_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('File name'),
      '#required' => TRUE,
      '#placeholder' => 'example.css',
      '#default_value' => $defaults['file_name'],
    ];

    $form['group'] = [
      '#type' => 'select',
      '#title' => $this->t('Group'),
      '#options' => [
        'base' => $this->t('Base'),
        'layout' => $this->t('Layout'),
        'component' => $this->t('Component'),
        'state' => $this->t('State'),
        'theme' => $this->t('Theme'),
      ],
      '#required' => TRUE,
      '#default_value' => $defaults['group'],
    ];

    $form['preprocess'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Preprocess'),
      '#default_value' => $defaults['preprocess'],
    ];

    $form['minified'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Minified'),
      '#default_value' => $defaults['minified'],
    ];

    $form['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight'),
      '#default_value' => $defaults['weight'],
      '#delta' => 10,
    ];

    $form['external'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('External'),
      '#default_value' => $defaults['external'],
    ];

    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Url'),
      '#default_value' => $defaults['url'],
      '#states' => ['visible' => [':input[name="external"]' => ['checked' => TRUE]]],
    ];

    $form['code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Code'),
      '#default_value' => $defaults['code'],
      '#attributes' => [
        'class' => ['library-definition-edit-code'],
      ],
      '#rows' => 15,
      '#codemirror' => [
        'mode' => 'css',
        'lineNumbers' => TRUE,
        'buttons' => [
          'undo',
          'redo',
          'enlarge',
          'shrink',
        ],
      ],
      '#states' => ['visible' => [':input[name="external"]' => ['checked' => FALSE]]],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $file_name = $form_state->getValue('file_name');
    if (!preg_match('#^\w[\w-\.\/]*\.css$#i', $file_name) || strpos($file_name, '..') !== FALSE) {
      $form_state->setError($form['file_name'], $this->t('The file name is not correct.'));
    }
  }

  /**
   * Returns the action form element for the current entity form.
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {

    $element = parent::actionsElement($form, $form_state);

    $file_id = $form['file_id']['#value'];
    if (isset($file_id)) {
      // Change link url to point on CSS delete form instead of entity delete
      // form.
      $route_parameters = [
        'library_definition' => $this->entity->id(),
        'file_id' => $form['file_id']['#value'],
      ];
      $element['delete']['#url'] = Url::fromRoute('entity.library_definition.delete_css_form', $route_parameters);
    }
    else {
      $element['delete']['#access'] = FALSE;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $file_id = $values['file_id'];

    $css = $this->entity->get('css');
    if (!$file_id) {
      $ids = array_keys($css);
      $file_id = count($ids) > 0 ? max($ids) + 1 : 1;
    }

    $css[$file_id] = [
      'file_name' => $values['file_name'],
      'group' => $values['group'],
      'preprocess' => $values['preprocess'],
      'minified' => $values['minified'],
      'weight' => $values['weight'],
      'external' => $values['external'],
      'code' => $values['code'],
      'url' => $values['url'],
    ];

    $this
      ->entity
      ->set('css', $css)
      ->save();

    $this->messenger()->addStatus($this->t('The CSS file has been saved.'));

    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
  }

}
