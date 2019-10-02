<?php

namespace Drupal\group\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GroupSettingsForm.
 */
class GroupSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'group_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['group.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('group.settings');
    $form['use_admin_theme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use admin theme'),
      '#description' => $this->t("Enables the administration theme for editing groups, members, etc."),
      '#default_value' => $config->get('use_admin_theme'),
    ];
    $form['redirect_to'] = [
      '#type' => 'radios',
      '#title' => $this->t('On entity creation, redirect to'),
      '#description' => $this->t('When an entity is created via the UI, the user will, by default, be directed to the created entity or the group content entity display.'),
      '#default_value' => $config->get('redirect_to'),
      '#options' => array(
        'entity' => $this->t('The created entity'),
        'group_content_entity' => $this->t('The created group content entity'),
        'group' => $this->t('The group the entity belongs to'),
      ),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('group.settings');
    $conf_admin_theme = $config->get('use_admin_theme');
    $form_admin_theme = $form_state->getValue('use_admin_theme');

    // Only rebuild the routes if the admin theme switch has changed.
    if ($conf_admin_theme != $form_admin_theme) {
      $config->set('use_admin_theme', $form_admin_theme)->save();
      \Drupal::service('router.builder')->setRebuildNeeded();
    }

    $config_redirect_to = $config->get('redirect_to');
    $form_redirect_to = $form_state->getValue('redirect_to');
    if ($config_redirect_to != $form_redirect_to) {
      $config->set('redirect_to', $form_redirect_to)->save();
    }

    parent::submitForm($form, $form_state);
  }

}
