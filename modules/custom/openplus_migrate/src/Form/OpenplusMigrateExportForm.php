<?php

namespace Drupal\openplus_migrate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openplus_migrate\Util\ConfigUtil;
use Drupal\migrate_plus\Entity\MigrationGroupInterface;
use Drupal\migrate_plus\Entity\MigrationGroup;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StreamWrapper\PrivateStream;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Builds export migration form
 */
class OpenplusMigrateExportForm extends FormBase {

  /**
   * The config storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Constructs a new ExportForm.
   *
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   *   The config storage.
   */
  public function __construct(StorageInterface $config_storage) {
    $this->configStorage = $config_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openplus_migrate_migration_export';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, MigrationGroupInterface $migration_group = NULL) {
    $form['migration_group'] = array(
      '#type' => 'hidden',
      '#value' => $migration_group->id(),
    );

    $form['message'] = array(
      '#type' => 'markup',
      '#markup' => $this->t('Export the following migration group and migrations within it to a downloadable package: @group', ['@group' => $migration_group->label()]),
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Export'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $output = array();
    // get the migration group config entity
    $group = $form_state->getValue('migration_group');
    $output['migration_group'] = $this->configStorage->read('migrate_plus.migration_group.' . $group);

    // get all migrations from the group
    $migrations = \Drupal::entityQuery('migration')
      ->condition('migration_group', $group)
      ->execute();
    foreach ($migrations as $migration) {
      $output['migrations'][] = $this->configStorage->read('migrate_plus.migration.' . $migration);
    }

    $yml = Yaml::encode($output);
    $directory =  'public://migration_exports';
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    $destination = $directory . '/' . $group . '.bin';
    $file = file_unmanaged_save_data($yml, $destination, FILE_EXISTS_REPLACE);
  
    // create download link
    $link = Link::fromTextAndUrl('download', Url::fromUri(file_create_url($file)));

    $this->messenger()->addMessage($this->t('Export complete, you may now @download this package.', ['@download' => $link->toString()]));
  }
}
