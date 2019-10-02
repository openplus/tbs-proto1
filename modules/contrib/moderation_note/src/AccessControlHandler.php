<?php

namespace Drupal\moderation_note;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the moderation_note entity type.
 *
 * @see \Drupal\moderation_note\Entity\ModerationNoteInterface
 */
class AccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access moderation notes')
          ->andIf($entity->getModeratedEntity()->access('view', $account, TRUE))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'create':
        return AccessResult::allowedIfHasPermission($account, 'create moderation notes')
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'update':
        return AccessResult::allowedIf($account->id() && $account->id() === $entity->getOwner()->id())
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->andIf(AccessResult::allowedIf($entity->isPublished()))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'delete':
        return AccessResult::allowedIf($account->id() && $account->id() === $entity->getOwner()->id())
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->andIf(AccessResult::allowedIf($entity->hasParent() || !$entity->isPublished()))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'resolve':
        return AccessResult::allowedIf($account->id() && $account->id() === $entity->getOwner()->id())
          ->orIf(AccessResult::allowedIfHasPermission($account, 'administer moderation notes'))
          ->andIf(AccessResult::allowedIf(!$entity->hasParent()))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      default:
        // No opinion.
        return AccessResult::neutral()->cachePerPermissions();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create moderation notes');
  }

}
