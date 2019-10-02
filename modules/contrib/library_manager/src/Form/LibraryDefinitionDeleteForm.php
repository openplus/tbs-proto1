<?php

namespace Drupal\library_manager\Form;

use Drupal\Core\Entity\EntityDeleteForm;

/**
 * Builds the form to delete a library definition.
 */
class LibraryDefinitionDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return '_confirm_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the library definition %id?', ['%id' => $this->entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    return t('Deleted library definition %id.', ['%id' => $this->entity->id()]);
  }

}
