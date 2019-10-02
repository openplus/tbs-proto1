<?php

namespace Drupal\entity_clone\EntityClone\Content;

use Drupal\Core\Entity\EntityInterface;

/**
 * Class TaxonomyTermEntityClone.
 */
class TaxonomyTermEntityClone extends ContentEntityCloneBase {

  /**
   * {@inheritdoc}
   */
  public function cloneEntity(EntityInterface $entity, EntityInterface $cloned_entity, array $properties = [], array &$already_cloned = []) {
    /** @var \Drupal\core\Entity\ContentEntityInterface $cloned_entity */

    // Enforce a parent if the cloned term doesn't have a parent.
    // (First level of a taxonomy tree).
    if (!isset($cloned_entity->parent->target_id)) {
      $cloned_entity->set('parent', 0);
    }
    return parent::cloneEntity($entity, $cloned_entity, $properties);
  }

}
