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

    $form[ConfigUtil::HARVESTER_DOMAIN_SETTING] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Harvester domain'),
      '#description' => $this->t('Enter the harvester domain, prefixed with <code>http://</code> or <code>https://</code>.'),
      '#default_value' => ConfigUtil::GetHarvesterDomain(),
      '#required' => TRUE,
    );

    $form[ConfigUtil::HARVESTER_PORT_SETTING] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Harvester port'),
      '#description' => $this->t('Enter the harvester port.'),
      '#default_value' => ConfigUtil::GetHarvesterPort(),
      '#required' => TRUE,
    );

    $form[ConfigUtil::HARVESTER_API_URL_SETTING] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Harvester API path'),
      '#description' => $this->t('Ex. /nodejs/export'),
      '#default_value' => ConfigUtil::GetHarvesterApiUrl(),
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
      ->set(ConfigUtil::HARVESTER_DOMAIN_SETTING, $form_state->getValue(ConfigUtil::HARVESTER_DOMAIN_SETTING))
      ->set(ConfigUtil::HARVESTER_PORT_SETTING, $form_state->getValue(ConfigUtil::HARVESTER_PORT_SETTING))
      ->set(ConfigUtil::HARVESTER_API_URL_SETTING, $form_state->getValue(ConfigUtil::HARVESTER_API_URL_SETTING))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
