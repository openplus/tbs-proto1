<?php

namespace Drupal\openplus_migrate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openplus_migrate\Util\ConfigUtil;

/**
 * Configure example settings for this site.
 */
class OpenplusMigrateSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openplus_migrate_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      ConfigUtil::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form[ConfigUtil::HARVESTER_API_URL_SETTING] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Harvester API URL'),
      '#description' => $this->t('Enter the URL for the harvester API endpoints, prefixed with <code>http://</code> or <code>https://</code>.'),
      '#default_value' => ConfigUtil::GetHarvesterBaseUrl(),
      '#required' => TRUE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration
    $this->configFactory->getEditable(ConfigUtil::CONFIG_NAME)
    // Set the submitted configuration setting
      ->set(ConfigUtil::HARVESTER_API_URL_SETTING, $form_state->getValue(ConfigUtil::HARVESTER_API_URL_SETTING))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
