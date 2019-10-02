<?php

namespace Drupal\library_manager\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of library definitions.
 */
class LibraryDefinitionListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Machine name');
    $header['version'] = $this->t('Version');
    $header['license'] = $this->t('License');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\library_manager\LibraryDefinitionInterface $entity */
    $row['id'] = $entity->id();
    $row['version'] = $entity->get('version');
    $row['license'] = $entity->get('license')['name'];
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['duplicate'] = [
      'title' => $this->t('Duplicate'),
      'weight' => 50,
      'url' => $entity->toUrl('duplicate-form'),
    ];
    return $operations;
  }

}
