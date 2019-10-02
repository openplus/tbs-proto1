<?php

namespace Drupal\entity_clone\EntityClone\Content;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\entity_clone\EntityClone\EntityCloneFormInterface;
use Drupal\entity_clone\EntityCloneSettingsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentEntityCloneFormBase.
 */
class ContentEntityCloneFormBase implements EntityHandlerInterface, EntityCloneFormInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translationManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The entity clone settings manager service.
   *
   * @var \Drupal\entity_clone\EntityCloneSettingsManager
   */
  protected $entityCloneSettingsManager;

  /**
   * Entities we've found while cloning.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface[]
   */
  protected $discoveredEntities = [];

  /**
   * Constructs a new ContentEntityCloneFormBase.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationManager $translation_manager
   *   The string translation manager.
   * @param \Drupal\entity_clone\EntityCloneSettingsManager $entity_clone_settings_manager
   *   The entity clone settings manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    TranslationManager $translation_manager,
    EntityCloneSettingsManager $entity_clone_settings_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->translationManager = $translation_manager;
    $this->entityCloneSettingsManager = $entity_clone_settings_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('entity_clone.settings.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(EntityInterface $entity, $parent = TRUE, &$discovered_entities = []) {
    $form = [
      'recursive' => [],
    ];

    if ($entity instanceof FieldableEntityInterface) {
      $discovered_entities[$entity->getEntityTypeId()][$entity->id()] = $entity;
      foreach ($entity->getFieldDefinitions() as $field_id => $field_definition) {
        if ($field_definition instanceof FieldConfigInterface && in_array($field_definition->getType(), ['entity_reference', 'entity_reference_revisions'], TRUE)) {
          $field = $entity->get($field_id);
          /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $value */
          if ($field->count() > 0) {
            $form['recursive'] = array_merge($form['recursive'], $this->getRecursiveFormElement($field_definition, $field_id, $field, $discovered_entities));
          }
        }
      }

      if ($parent) {
        $form = array_merge([
          'description' => [
            '#markup' => $this->getFormDescription($form, $entity),
            '#access' => TRUE,
          ],
        ], $form);
      }
    }

    return $form;
  }

  /**
   * Get the recursive form element.
   *
   * @param \Drupal\Core\Field\FieldConfigInterface $field_definition
   *   The field definition.
   * @param string $field_id
   *   The field ID.
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field item.
   * @param array $discovered_entities
   *   List of all entities already discovered.
   *
   * @return array
   *   The form element for a recursive clone.
   */
  protected function getRecursiveFormElement(FieldConfigInterface $field_definition, $field_id, FieldItemListInterface $field, array &$discovered_entities) {
    $form_element = [
      '#tree' => TRUE,
    ];

    $fieldset_access = !$this->entityCloneSettingsManager->getHiddenValue($field_definition->getFieldStorageDefinition()->getSetting('target_type'));
    $form_element[$field_definition->id()] = [
      '#type' => 'fieldset',
      '#title' => $this->translationManager->translate('Entities referenced by field <em>@label (@field_id)</em>.', [
        '@label' => $field_definition->label(),
        '@field_id' => $field_id,
      ]),
      '#access' => $fieldset_access,
      '#description_should_be_shown' => $fieldset_access,
    ];

    foreach ($field as $value) {
      // Check if we're not dealing with an entity
      // That has been deleted in the meantime.
      if (!$referenced_entity = $value->get('entity')->getTarget()) {
        continue;
      }
      /** @var \Drupal\Core\Entity\ContentEntityInterface $referenced_entity */
      $referenced_entity = $value->get('entity')->getTarget()->getValue();

      if (isset($discovered_entities[$referenced_entity->getEntityTypeId()]) && array_key_exists($referenced_entity->id(), $discovered_entities[$referenced_entity->getEntityTypeId()])) {
        $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['is_circular'] = [
          '#type' => 'hidden',
          '#value' => TRUE,
        ];
        $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['circular'] = [
          '#type' => 'item',
          '#markup' => $this->translationManager->translate('Circular reference detected'),
        ];
      }
      else {
        $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['clone'] = [
          '#type' => 'checkbox',
          '#title' => $this->translationManager->translate('Clone entity <strong>ID:</strong> <em>@entity_id</em>, <strong>Type:</strong> <em>@entity_type - @bundle</em>, <strong>Label:</strong> <em>@entity_label</em>', [
            '@entity_id' => $referenced_entity->id(),
            '@entity_type' => $referenced_entity->getEntityTypeId(),
            '@bundle' => $referenced_entity->bundle(),
            '@entity_label' => $referenced_entity->label(),
          ]),
          '#default_value' => $this->entityCloneSettingsManager->getDefaultValue($referenced_entity->getEntityTypeId()),
          '#access' => $referenced_entity->access('view label'),
        ];

      if ($this->entityCloneSettingsManager->getDisableValue($referenced_entity->getEntityTypeId())) {
        $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['clone']['#attributes'] = [
          'disabled' => TRUE,
          ];
          $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['clone']['#value'] = $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['clone']['#default_value'];
        }

        $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['target_entity_type_id'] = [
          '#type' => 'hidden',
          '#value' => $referenced_entity->getEntityTypeId(),
        ];

        $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['target_bundle'] = [
          '#type' => 'hidden',
          '#value' => $referenced_entity->bundle(),
        ];
        if ($referenced_entity instanceof ContentEntityInterface) {
          $form_element[$field_definition->id()]['references'][$referenced_entity->id()]['children'] = $this->getChildren($referenced_entity, $discovered_entities);
        }
      }
    }

    return $form_element;
  }

  /**
   * Fetches clonable children from a field.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $referenced_entity
   *   The field item list.
   * @param array $discovered_entities
   *   List of all entities already discovered.
   *
   * @return array
   *   The list of children.
   */
  protected function getChildren(ContentEntityInterface $referenced_entity, array &$discovered_entities) {
    /** @var \Drupal\entity_clone\EntityClone\EntityCloneFormInterface $entity_clone_handler */
    if ($this->entityTypeManager->hasHandler($referenced_entity->getEntityTypeId(), 'entity_clone_form')) {
      // Record that we've found this entity.
      $discovered_entities[$referenced_entity->getEntityTypeId()][$referenced_entity->id()] = $referenced_entity;

      $entity_clone_form_handler = $this->entityTypeManager->getHandler($referenced_entity->getEntityTypeId(), 'entity_clone_form');
      return $entity_clone_form_handler->formElement($referenced_entity, FALSE, $discovered_entities);
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getValues(FormStateInterface $form_state) {
    return $form_state->getValues();
  }

  /**
   * Get the clone form confirmation page description.
   *
   * If there are no recursive elements visible, the default description should
   * be shown.
   *
   * @param array $form
   *   The clone form.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Description to be shown
   */
  protected function getFormDescription(array $form, EntityInterface $entity) {
    $has_recursive = FALSE;
    $elements_visible = FALSE;

    if (isset($form['recursive'])) {
      $has_recursive = TRUE;
    }

    array_walk_recursive($form['recursive'], function ($item, $key) use (&$elements_visible) {
      if ($key === '#description_should_be_shown') {
        $elements_visible = ($elements_visible || $item);
      }
    });

    if ($has_recursive && $elements_visible) {
      return $this->translationManager->translate("
            <p>Specify the child entities (the entities referenced by this entity) that should also be cloned as part of
            the cloning process.  If they're not included, these fields' referenced entities will be the same as in the
            original.  In other words, fields in both the original entity and the cloned entity will refer to the same
            referenced entity.  Examples:</p>

            <p>If you have a Paragraph field in your entity, and you choose not to clone it here, deleting the original
            or cloned entity will also delete the Paragraph field from the other one.  So you probably want to clone
            Paragraph fields.</p>

            <p>However, if you have a User reference field, you probably don't want to clone it here because a new User
            will be created for referencing by the clone.</p>

            <p>Some options may be disabled here, preventing you from changing them, as set by your administrator.  Some
            options may also be missing, hidden by your administrator, forcing you to clone with the default settings.
            It's possible that there are no options here for you at all, or none need to be set, in which case you may
            simply hit the <em>Clone</em> button.</p>
          ");
    }
    else {
      return $this->translationManager->translate("<p>Do you want to clone the <em>@type</em> entity named <em>@title</em></p>", [
        "@type" => $entity->getEntityType()->getLabel(),
        "@title" => $entity->label(),
      ]);
    }

  }

}
