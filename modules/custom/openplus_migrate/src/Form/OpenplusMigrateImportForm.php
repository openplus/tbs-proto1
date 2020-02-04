<?php

namespace Drupal\openplus_migrate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openplus_migrate\Util\ConfigUtil;
use Drupal\migrate_plus\Entity\MigrationGroupInterface;
use Drupal\migrate_plus\Entity\MigrationGroup;
use Drupal\migrate_plus\Entity\Migration;
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
class OpenplusMigrateImportForm extends FormBase {

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

    $form['message'] = array(
      '#type' => 'markup',
      '#markup' => $this->t('Import migration group and migrations within it.'),
    );

    $form['migration_upload'] = [
      '#type' => 'file',
      '#title' => $this->t('Migration file to be imported:'),
      '#description' => $this->t('Allowed types: @extensions.', ['@extensions' => 'bin']),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $all_files = $this->getRequest()->files->get('files', []);
    if (!empty($all_files['migration_upload'])) {
      $file_upload = $all_files['migration_upload'];
      if ($file_upload->isValid()) {
        $form_state->setValue('migration_upload', $file_upload->getRealPath());
        return;
      }
    }

    $form_state->setErrorByName('migration_upload', $this->t('The file could not be uploaded.'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if ($path = $form_state->getValue('migration_upload')) {
      $items = Yaml::decode(file_get_contents($path));
      $migration_group = $items['migration_group'];
      $entity_type ='migration_group';
      $entity = \Drupal::entityTypeManager()
        ->getStorage($entity_type)
        ->load($migration_group['id']);

      // remove old one, this also deletes the migrations within it
      if ($entity) {
        \Drupal::logger('openplus_migrate')->notice('Removing existing group.');
        $entity->delete();
      }
      $entity = MigrationGroup::create($migration_group);
      $entity->save();

      $entity_type ='migration';
      foreach ($items['migrations'] as $migration) {
        $entity = Migration::create($migration);
        $entity->save();

      }

      $this->messenger()->addMessage($this->t('Import complete.'));
    }
  }
}
