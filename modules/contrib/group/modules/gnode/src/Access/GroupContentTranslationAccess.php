<?php

namespace Drupal\gnode\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;

/**
 * Access check for entity translation overview.
 */
class GroupContentTranslationAccess implements AccessInterface {

  /**
   * Checks access to the translation overview for the entity and bundle.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(RouteMatchInterface $route_match, AccountInterface $account, $entity_type_id) {
    /* @var \Drupal\node\NodeInterface $node */
    $node = $route_match->getParameter($entity_type_id);
    if ($entity_type_id === 'node' && $node) {
      // Check if it this is group content.
      $group_content_array = GroupContent::loadByEntity($node);
      $group_ids = [];
      foreach ($group_content_array as $group_content) {
        $group_ids[] = $group_content->gid->target_id;
      }
      if (!empty($group_ids)) {
        // This is group content. Check for the group permission to translate.
        $groups = Group::loadMultiple($group_ids);
        $plugin_id = 'group_node:' . $node->bundle();
        /** @var \Drupal\group\Entity\Group[] $groups */
        foreach ($groups as $group) {
          if ($group->hasPermission("translate $plugin_id entity", $account) && $node->access('update', $account)) {
            return AccessResult::allowed();
          }
        }

        return AccessResult::forbidden('You are not allowed to translate group content');
      }
    }

    // There is no entity available or the entity is not part of any group.
    return AccessResult::allowed();
  }

}
