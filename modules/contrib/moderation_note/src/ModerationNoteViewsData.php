<?php

namespace Drupal\moderation_note;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the entity.
 */
class ModerationNoteViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['moderation_note']['table']['provider'] = 'moderation_note';

    $data['moderation_note']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Moderation Note'),
      'help' => $this->t('Moderation Note'),
      'weight' => -10,
    ];

    $data['moderation_note']['node'] = [
      'real field' => 'entity_id',
      'relationship' => [
        'title' => $this->t('Notated Content'),
        'help' => $this->t('Content the Moderation Note it is attached to.'),
        'base' => 'node_field_data',
        'base field' => 'nid',
        'id' => 'standard',
        'label' => $this->t('Notated Content'),
        'extra' => [[
          'left_field' => 'entity_langcode',
          'field' => 'langcode',
        ],
        ],
      ],
    ];

    return $data;
  }

}
