<?php

namespace Drupal\entity_clone\EntityClone;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a common interface for all entity clone form objects.
 */
interface EntityCloneFormInterface {

  /**
   * Get all specific form element.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param bool $parent
   *   Is the parent form element.
   *
   * @return array
   *   The form elements.
   */
  public function formElement(EntityInterface $entity, $parent = TRUE);

  /**
   * Get all new values provided by the specific form element.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   An array containing all new values.
   */
  public function getValues(FormStateInterface $form_state);

}
