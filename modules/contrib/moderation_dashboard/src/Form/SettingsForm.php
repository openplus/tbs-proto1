<?php

namespace Drupal\moderation_dashboard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The settings form for Moderation Dashboard.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['moderation_dashboard.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'moderation_dashboard_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('moderation_dashboard.settings');
    $form['redirect_on_login'] = [
      '#type' => 'checkbox',
      '#title' => t('Redirect on login'),
      '#default_value' => $config->get('redirect_on_login'),
      '#description' => $this->t('Redirect to moderation dashboard after login.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('moderation_dashboard.settings')
      ->set('redirect_on_login', $values['redirect_on_login'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
