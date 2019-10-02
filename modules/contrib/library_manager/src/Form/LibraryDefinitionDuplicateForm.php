<?php

namespace Drupal\library_manager\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\library_manager\Entity\LibraryDefinition;

/**
 * Form controller for the Views duplicate form.
 *
 * @property \Drupal\library_manager\LibraryDefinitionInterface $entity
 */
class LibraryDefinitionDuplicateForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    parent::form($form, $form_state);

    $form['#title'] = $this->t('Duplicate of @label', ['@label' => $this->entity->id()]);

    $form['id'] = [
      '#type' => 'machine_name',
      '#maxlength' => 128,
      '#machine_name' => [
        'exists' => '\Drupal\library_manager\Entity\LibraryDefinition::load',
      ],
    ];

    // Machine name uniqueness is not validated if the default value was not
    // changed. So let's leave it empty.
    $default_id = 'duplicate_of_' . $this->entity->id();
    if (!LibraryDefinition::load($default_id)) {
      $form['id']['#default_value'] = $default_id;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Duplicate'),
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity = $this->entity->createDuplicate();
    $this->entity->set('id', $form_state->getValue('id'));
    $this->entity->save();

    // Redirect the user to the view admin form.
    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
  }

}
