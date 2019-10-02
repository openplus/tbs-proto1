<?php

namespace Drupal\entity_clone\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Represents entity selection as event.
 */
class EntityCloneEvent extends Event {

  /**
   * Entity being cloned.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * New cloned entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $clonedEntity;

  /**
   * Properties.
   *
   * @var array
   */
  protected $properties;

  /**
   * Constructs an EntityCloneEvent object.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The original entity that was cloned.
   * @param \Drupal\Core\Entity\EntityInterface $cloned_entity
   *   The clone of the original entity.
   * @param array $properties
   *   The entity's properties.
   */
  public function __construct(EntityInterface $entity, EntityInterface $cloned_entity, array $properties = []) {
    $this->entity = $entity;
    $this->clonedEntity = $cloned_entity;
    $this->properties = $properties;
  }

  /**
   * Gets entity being cloned.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The original entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Gets new cloned entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The cloned entity.
   */
  public function getClonedEntity() {
    return $this->clonedEntity;
  }

  /**
   * Gets entity properties.
   *
   * @return array
   *   The list of properties.
   */
  public function getProperties() {
    return $this->properties;
  }

}
