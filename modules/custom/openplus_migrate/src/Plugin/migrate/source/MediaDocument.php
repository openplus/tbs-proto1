<?php

namespace Drupal\openplus_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for media document content.
 *
 * @MigrateSource(
 *   id = "media_document"
 * )
 */
class MediaDocument extends SqlBase {

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
    $query->condition('f.filemime', 'image%', 'NOT LIKE');
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

    return parent::prepareRow($row);
  }

}
