<?php

namespace Drupal\moderation_dashboard\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A Views field which provides a link to the latest version of an Entity.
 *
 * @ViewsField("link_to_latest_version")
 */
class LinkToLatestVersion extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\content_moderation\ModerationInformation $information */
    $moderation_information = \Drupal::service('content_moderation.moderation_information');

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getEntity($values);
    $entity_type_id = $entity->getEntityTypeId();

    if (
      $moderation_information->isModeratedEntity($entity) &&
      $moderation_information->hasPendingRevision($entity)
    ) {
      $entity = $moderation_information->getLatestRevision($entity_type_id, $entity->id());
      $build = [
        '#title' => $entity->label(),
        '#type' => 'link',
        '#url' => Url::fromRoute("entity.{$entity_type_id}.latest_version", [
          $entity_type_id => $entity->id(),
        ]),
      ];
    }
    else {
      $build = [
        '#title' => $entity->label(),
        '#type' => 'link',
        '#url' => $entity->toLink()->getUrl(),
      ];
    }

    return $build;
  }

}
