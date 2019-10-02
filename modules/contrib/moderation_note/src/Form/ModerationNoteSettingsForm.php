<?php

namespace Drupal\moderation_note\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Moderation Note settings for this site.
 */
class ModerationNoteSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'moderation_note_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['moderation_note.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('moderation_note.settings');

    $form['send_email'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send email notifications'),
      '#description' => $this->t('When notes are created, resolved, and replied to, an email notification can be sent to relevant users.'),
      '#default_value' => $config->get('send_email'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('moderation_note.settings');

    $config->set('send_email', $form_state->getValue('send_email'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
