<?php

/**
 * @file
 * Contains entity_clone.post_update.php.
 */

/**
 * Populates new entity_clone form settings.
 */
function entity_clone_post_update_populate_form_settings3() {
  $form_settings = \Drupal::configFactory()->get('entity_clone.settings')->get('form_settings');
  if (!$form_settings) {
    /** @var \Drupal\entity_clone\EntityCloneSettingsManager $entity_clone_settings_manager */
    $entity_clone_settings_manager = \Drupal::service('entity_clone.settings.manager');
    $form_settings = [];
    foreach (array_keys($entity_clone_settings_manager->getContentEntityTypes()) as $entity_type_id) {
      $form_settings[$entity_type_id] = [
        'default_value' => FALSE,
        'disable' => FALSE,
        'hidden' => FALSE,
      ];
    }

    \Drupal::configFactory()->getEditable('entity_clone.settings')->set('form_settings', $form_settings)->save();
  }
}
