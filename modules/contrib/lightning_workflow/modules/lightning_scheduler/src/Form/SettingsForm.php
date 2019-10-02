<?php

namespace Drupal\lightning_scheduler\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The settings form for controlling Lightning Scheduler's behavior.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lightning_scheduler.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_scheduler_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['time_step'] = [
      '#type' => 'select',
      '#title' => $this->t("The time input's step attribute"),
      '#options' => [
        1 => $this->formatPlural(1, '1 second', '@count seconds'),
        60 => $this->formatPlural(1, '1 minute', '@count minutes'),
        300 => $this->formatPlural(5, '1 minute', '@count minutes'),
        600 => $this->formatPlural(10, '1 minute', '@count minutes'),
        900 => $this->formatPlural(15, '1 minute', '@count minutes'),
        1800 => $this->formatPlural(30, '1 minute', '@count minutes'),
        3600 => $this->formatPlural(1, '1 hour', '@count hours'),
      ],
      '#required' => TRUE,
      '#default_value' => $this->config('lightning_scheduler.settings')->get('time_step'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('lightning_scheduler.settings')
      ->set('time_step', (int) $form_state->getValue('time_step'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
