<?php

namespace Drupal\library_manager\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Library definition js form.
 *
 * @property \Drupal\library_manager\LibraryDefinitionInterface $entity
 */
class LibraryDefinitionJsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $defaults = [
      'file_name' => '',
      'code' => '',
      'preprocess' => TRUE,
      'minified' => FALSE,
      'weight' => 0,
      'external' => FALSE,
      'url' => '',
    ];

    $route_match = $this->getRouteMatch();
    $file_id = $route_match->getParameter('file_id');
    if (!$route_match->getParameter('is_new')) {
      // This JS file should exist in the entity.
      $data = $this->entity->getJsFile($file_id);
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
      '#placeholder' => 'example.js',
      '#default_value' => $defaults['file_name'],
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

    $weights = range(-10, 0);
    $form['weight'] = [
      // Use 'select' because 'weight' element does not support '#min' property.
      '#type' => 'select',
      '#title' => $this->t('Weight'),
      '#default_value' => $defaults['weight'],
      '#options' => array_combine($weights, $weights),
    ];

    $form['external'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('External'),
      '#default_value' => $defaults['external'],
    ];

    $form['code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Code'),
      '#default_value' => $defaults['code'],
      '#attributes' => [
        'class' => ['library-definition-edit-code'],
      ],
      '#states' => ['visible' => [':input[name="external"]' => ['checked' => FALSE]]],
      '#rows' => 15,
      '#codemirror' => [
        'mode' => 'javascript',
        'lineNumbers' => TRUE,
        'buttons' => [
          'undo',
          'redo',
          'enlarge',
          'shrink',
        ],
      ],
    ];

    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Url'),
      '#default_value' => $defaults['url'],
      '#states' => ['visible' => [':input[name="external"]' => ['checked' => TRUE]]],

    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $file_name = $form_state->getValue('file_name');
    if (!preg_match('#^\w[\w-\.\/]*\.js$#i', $file_name) || strpos($file_name, '..') !== FALSE) {
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
      // Change link url to point on JS delete form instead of entity delete
      // form.
      $route_parameters = [
        'library_definition' => $this->entity->id(),
        'file_id' => $form['file_id']['#value'],
      ];
      $element['delete']['#url'] = Url::fromRoute('entity.library_definition.delete_js_form', $route_parameters);
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

    $js = $this->entity->get('js');
    if (!$file_id) {
      $ids = array_keys($js);
      $file_id = count($ids) > 0 ? max($ids) + 1 : 1;
    }

    $js[$file_id] = [
      'file_name' => $values['file_name'],
      'preprocess' => $values['preprocess'],
      'minified' => $values['minified'],
      'weight' => $values['weight'],
      'external' => $values['external'],
      'code' => $values['code'],
      'url' => $values['url'],
    ];

    $this
      ->entity
      ->set('js', $js)
      ->save();

    $this->messenger()->addStatus($this->t('The JS file has been saved.'));

    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
  }

}
