<?php

namespace Drupal\openplus_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Post Processing of content.
 *
 * @MigrateSource(
 *   id = "post_process_all_nodes"
 * )
 */
class PostProcessAllNodes extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {

    $query = $this->select('node', 'n');
    $query ->fields('n',
      [
        'nid',
        'vid',
        'langcode',
        'type',
      ]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Revision ID'),
      'langcode' => $this->t('Language'),
      'type' => $this->t('Type'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    // Translation support.
    if (!empty($row->getSourceProperty('translations'))) {
      $row->setSourceProperty('langcode', 'fr');
    }

    // Title Field.
    $title = $this->select('node_field_data', 'fd')
      ->fields('fd', ['title'])
      ->condition('nid', $row->getSourceProperty('nid'))
      ->condition('vid', $row->getSourceProperty('vid'))
      ->condition('langcode', $row->getSourceProperty('langcode'))
      ->condition('type', $row->getSourceProperty('type'))
      ->execute()
      ->fetchCol();

    // Body.
    $body = $this->select('node__body', 'fd')
      ->fields('fd', ['body_value'])
      ->condition('entity_id', $row->getSourceProperty('nid'))
      ->condition('revision_id', $row->getSourceProperty('vid'))
      ->condition('langcode', $row->getSourceProperty('langcode'))
      ->condition('bundle', $row->getSourceProperty('type'))
      ->execute()
      ->fetchCol();

    if (!empty($title[0])) {
      $row->setSourceProperty('title', $title[0]);
    }
    elseif (!empty($row->getSourceProperty('translations'))) {
      return FALSE;
    }
    $row->setSourceProperty('body', $body[0]);

    return parent::prepareRow($row);
  }

}
