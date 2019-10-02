<?php

namespace Drupal\group;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Loader for wrapped GroupContent entities using the 'group_membership' plugin.
 *
 * Seeing as this class is part of the main module, we could have easily put its
 * functionality in GroupContentStorage. We chose not to because other modules
 * won't have that power and we should provide them with an example of how to
 * write such a plugin-specific GroupContent loader.
 *
 * Also note that we don't simply return GroupContent entities, but wrapped
 * copies of said entities, namely \Drupal\group\GroupMembership. In a future
 * version we should investigate the feasibility of extending GroupContent
 * entities rather than wrapping them.
 */
class GroupMembershipLoader implements GroupMembershipLoaderInterface {

  /**
   * Static cache of a user memberships per user.
   *
   * @var array
   */
  protected $userMemberships = [];

  /**
   * Static cache of group memberships per group.
   *
   * @var array
   */
  protected $groupMemberships = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user's account object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new GroupTypeController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Gets the group content storage.
   *
   * @return \Drupal\group\Entity\Storage\GroupContentStorageInterface
   */
  protected function groupContentStorage() {
    return $this->entityTypeManager->getStorage('group_content');
  }

  /**
   * Wraps GroupContent entities in a GroupMembership object.
   *
   * @param \Drupal\group\Entity\GroupContentInterface[] $entities
   *   An array of GroupContent entities to wrap.
   *
   * @return \Drupal\group\GroupMembership[]
   *   A list of GroupMembership wrapper objects.
   */
  protected function wrapGroupContentEntities($entities) {
    $group_memberships = [];
    foreach ($entities as $group_content) {
      $group_memberships[$group_content->gid->target_id] = new GroupMembership($group_content);
    }
    return $group_memberships;
  }

  /**
   * {@inheritdoc}
   */
  public function load(GroupInterface $group, AccountInterface $account) {
    $cache_id = md5($account->id());
    if (!isset($this->userMemberships[$account->id()][$cache_id])) {
      $this->loadByUser($account);
    }
    return isset($this->userMemberships[$account->id()][$cache_id][$group->id()]) ? $this->userMemberships[$account->id()][$cache_id][$group->id()] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function loadByGroup(GroupInterface $group, $roles = NULL) {
    $cache_id = md5($group->id());
    if ($roles) {
      $cache_id = is_array($roles) ? ':' . md5(implode('-', $roles)) : ':' . md5($roles);
    }

    if (isset($this->groupMemberships[$group->id()][$cache_id])) {
      return $this->groupMemberships[$group->id()][$cache_id];
    }

    $filters = [];
    if (isset($roles)) {
      $filters['group_roles'] = (array) $roles;
    }

    $group_contents = $this->groupContentStorage()->loadByGroup($group, 'group_membership', $filters);
    $this->groupMemberships[$group->id()][$cache_id] = $this->wrapGroupContentEntities($group_contents);

    return $this->groupMemberships[$group->id()][$cache_id];
  }

  /**
   * {@inheritdoc}
   */
  public function loadByUser(AccountInterface $account = NULL, $roles = NULL) {
    $cache_id = md5($account->id());
    if ($roles) {
      $cache_id = is_array($roles) ? ':' . md5(implode('-', $roles)) : ':' . md5($roles);
    }

    if (isset($this->userMemberships[$account->id()][$cache_id])) {
      return $this->userMemberships[$account->id()][$cache_id];
    }

    if (!isset($account)) {
      $account = $this->currentUser;
    }

    // Load all group content types for the membership content enabler plugin.
    $group_content_types = $this->entityTypeManager
      ->getStorage('group_content_type')
      ->loadByProperties(['content_plugin' => 'group_membership']);

    // If none were found, there can be no memberships either.
    if (empty($group_content_types)) {
      $this->userMemberships[$account->id()][$cache_id] = [];
      return $this->userMemberships[$account->id()][$cache_id];
    }

    // Try to load all possible membership group content for the user.
    $group_content_type_ids = [];
    foreach ($group_content_types as $group_content_type) {
      $group_content_type_ids[] = $group_content_type->id();
    }

    $properties = ['type' => $group_content_type_ids, 'entity_id' => $account->id()];
    if (isset($roles)) {
      $properties['group_roles'] = (array) $roles;
    }

    /** @var \Drupal\group\Entity\GroupContentInterface[] $group_contents */
    $group_contents = $this->groupContentStorage()->loadByProperties($properties);
    $this->userMemberships[$account->id()][$cache_id] = $this->wrapGroupContentEntities($group_contents);
    return $this->userMemberships[$account->id()][$cache_id];
  }

  /**
   * {@inheritdoc}
   */
  public function resetUserStaticCache(AccountInterface $account) {
    unset($this->userMemberships[$account->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function resetGroupStaticCache(GroupInterface $group) {
    unset($this->groupMemberships[$group->id()]);
  }

}
