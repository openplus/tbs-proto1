<?php

namespace Drupal\lightning_scheduler;

use Drupal\content_moderation\Permissions as BasePermissions;

class Permissions extends BasePermissions {

  /**
   * {@inheritdoc}
   */
  public function transitionPermissions() {
    $permissions = parent::transitionPermissions();

    foreach ($permissions as $permission => $info) {
      unset($permissions[$permission]);

      $permission = preg_replace('/^use /', 'schedule ', $permission);

      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $title */
      $title = $info['title'];
      $info['title'] = $this->t('%workflow workflow: Schedule %transition transition.', $title->getArguments());

      $permissions[$permission] = $info;
    }
    return $permissions;
  }

}
