<?php

namespace Drupal\library_manager\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Library definition JS form.
 *
 * @property \Drupal\library_manager\LibraryDefinitionInterface $entity
 */
class LibraryDefinitionJsDeleteForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $file_id = $this->getRouteMatch()->getParameter('file_id');

    $form['#title'] = $this->t('Are you sure you want to delete the file?');

    $form['#attributes']['class'][] = 'confirmation';
    $form['description'] = [
      '#markup' => $this->t('This action cannot be undone.'),
    ];

    $form['file_id'] = [
      '#type' => 'value',
      '#value' => $file_id,
    ];

    if (!isset($form['#theme'])) {
      $form['#theme'] = 'confirm_form';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);

    $element['submit']['#value'] = $this->t('Delete');
    $element['delete']['#access'] = FALSE;

    $element['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => $this->entity->toUrl('edit-form'),
      '#weight' => 10,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $file_id = $form_state->getValue('file_id');

    $js = $this->entity->get('js');
    unset($js[$file_id]);

    $this
      ->entity
      ->set('js', $js)
      ->save();

    $this->messenger()->addStatus($this->t('The JS file has been deleted.'));

    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
  }

}
