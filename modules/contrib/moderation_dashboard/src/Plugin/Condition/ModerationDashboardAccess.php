<?php

namespace Drupal\moderation_dashboard\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;

/**
 * Provides a 'Moderation Dashboard Access' condition.
 *
 * @Condition(
 *   id = "moderation_dashboard_access",
 *   label = @Translation("Moderation Dashboard Access"),
 *   context = {
 *     "dashboard_user" = @ContextDefinition("entity:user", label = @Translation("Dashboard owner")),
 *     "current_user" = @ContextDefinition("entity:user", label = @Translation("Current user")),
 *   }
 * )
 */
class ModerationDashboardAccess extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $dashboard_owner = $this->getContextValue('dashboard_user');
    $current_user = $this->getContextValue('current_user');

    // If the given user doesn't have a dashboard, nobody can view it.
    if (!$dashboard_owner->hasPermission('use moderation dashboard')) {
      return FALSE;
    }

    // If the current user is on their own dashboard, they can view it.
    if ($current_user->id() === $dashboard_owner->id()) {
      return TRUE;
    }

    // But they can only view the dashboard of others with another permission.
    return $current_user->hasPermission('view any moderation dashboard');
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if ($this->isNegated()) {
      return $this->t("User can't access moderation dashboard.");
    }

    return $this->t('User can access moderation dashboard.');
  }

}
