<?php

namespace Drupal\moderation_note\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\PhpSelection;
use Drupal\user\Entity\User;

/**
 * A selection handler that only returns users with access to Moderation Notes.
 *
 * @EntityReferenceSelection(
 *   id = "moderation_note:user",
 *   label = @Translation("Select users who can access moderation notes"),
 *   group = "moderation_note",
 *   entity_types = {"user"},
 *   weight = 1
 * )
 */
class ModerationNoteUserSelection extends PhpSelection {

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $options = parent::getReferenceableEntities($match, $match_operator, $limit);
    $filtered_options = [];
    foreach ($options as $bundle => &$items) {
      foreach ($items as $entity_id => $label) {
        $user = User::load($entity_id);
        if ($user && $user->hasPermission('access moderation notes')) {
          $filtered_options[$bundle][$entity_id] = $label;
        }
      }
    }
    return $filtered_options;
  }

}
