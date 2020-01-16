<?php

namespace Drupal\openplus_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for media image content.
 *
 * @MigrateSource(
 *   id = "media_image"
 * )
 */
class MediaImage extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $migration_table = 'migrate_map_maas__mdf__en__' . str_replace('-', '_', $this->configuration['migration_uuid']);

    $query = $this->select('file_managed', 'f')->fields('f');
    $schema = \Drupal::database()->schema();
    // if the migration has not run once this table will not exist and the admin migration page will whitescreen
    if ($schema->tableExists($migration_table)) {
      $query->innerJoin($migration_table, 'm', 'f.fid = m.destid1');
    }
    $query->condition('f.filemime', 'image%', 'LIKE');
    $query->orderBy('f.fid');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'fid' => $this->t('File ID'),
      'uid' => $this->t('The {users}.uid who added the file. If set to 0, this file was added by an anonymous user.'),
      'filename' => $this->t('Filename'),
      'uri' => $this->t('URI'),
      'filemime' => $this->t('File MIME Type'),
      'status' => $this->t('The published status of a file.'),
      'timestamp' => $this->t('The time that the file was added.'),
      'type' => $this->t('The type of this file.'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'fid' => [
        'type' => 'integer',
        'alias' => 'f',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    // Translation support.
    if (!empty($row->getSourceProperty('translations'))) {
      $row->setSourceProperty('language', 'fr');
    }
    else {
      $row->setSourceProperty('language', 'en');
    }

/* TODO: Replace D7 alt and title extraction code with D8.
    // Alt Field.
    $alt = $this->select('field_data_field_file_image_alt_text', 'db')
      ->fields('db', ['field_file_image_alt_text_value'])
      ->condition('entity_type', 'file')
      ->condition('bundle', 'image')
      ->condition('entity_id', $row->getSourceProperty('fid'))
      ->condition('revision_id', $row->getSourceProperty('fid'))
      ->condition('language', 'en')
      ->execute()
      ->fetchCol();

    $altFr = $this->select('field_data_field_file_image_alt_text', 'db')
      ->fields('db', ['field_file_image_alt_text_value'])
      ->condition('entity_type', 'file')
      ->condition('bundle', 'image')
      ->condition('entity_id', $row->getSourceProperty('fid'))
      ->condition('revision_id', $row->getSourceProperty('fid'))
      ->condition('language', 'fr')
      ->execute()
      ->fetchCol();

    // Title Field.
    $title = $this->select('field_data_field_file_image_title_text', 'db')
      ->fields('db', ['field_file_image_title_text_value'])
      ->condition('entity_type', 'file')
      ->condition('bundle', 'image')
      ->condition('entity_id', $row->getSourceProperty('fid'))
      ->condition('revision_id', $row->getSourceProperty('fid'))
      ->condition('language', 'en')
      ->execute()
      ->fetchCol();

    $titleFr = $this->select('field_data_field_file_image_title_text', 'db')
      ->fields('db', ['field_file_image_title_text_value'])
      ->condition('entity_type', 'file')
      ->condition('bundle', 'image')
      ->condition('entity_id', $row->getSourceProperty('fid'))
      ->condition('revision_id', $row->getSourceProperty('fid'))
      ->condition('language', 'fr')
      ->execute()
      ->fetchCol();

    $row->setSourceProperty('alt', $alt[0]);
    $row->setSourceProperty('title', $title[0]);

    $row->setSourceProperty('alt_fr', $altFr[0]);
    $row->setSourceProperty('title_fr', $titleFr[0]);
*/

    return parent::prepareRow($row);
  }

}
