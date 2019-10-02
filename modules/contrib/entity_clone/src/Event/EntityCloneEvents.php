<?php

namespace Drupal\entity_clone\Event;

/**
 * Contains all events thrown by entity clone.
 */
final class EntityCloneEvents {

  /**
   * The PRE_CLONE event occurs before the entity was cloned.
   *
   * @var string
   */
  const PRE_CLONE = 'entity_clone.pre_clone';

  /**
   * The POST_CLONE event occurs when an entity has been cloned and saved.
   *
   * @var string
   */
  const POST_CLONE = 'entity_clone.post_clone';

}
