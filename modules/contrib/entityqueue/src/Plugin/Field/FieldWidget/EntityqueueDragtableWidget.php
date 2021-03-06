<?php

namespace Drupal\entityqueue\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'entityqueue_dragtable' widget.
 *
 * @FieldWidget(
 *   id = "entityqueue_dragtable",
 *   label = @Translation("Autocomplete (draggable table) - Experimental"),
 *   description = @Translation("An autocomplete text field with a draggable table."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityqueueDragtableWidget extends EntityReferenceAutocompleteWidget {

  /**
   * The unique HTML ID of the widget's wrapping element.
   *
   * @var string
   */
  protected $wrapperId;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'link_to_entity' => FALSE,
      'link_to_edit_form' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['link_to_entity'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link label to the referenced entity'),
      '#default_value' => $this->getSetting('link_to_entity'),
    ];
    $elements['link_to_edit_form'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add a link to the edit form of the referenced entity'),
      '#default_value' => $this->getSetting('link_to_edit_form'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $settings = $this->getSettings();
    if (!empty($settings['link_to_entity'])) {
      $summary[] = $this->t('Link to the referenced entity');
    }
    if (!empty($settings['link_to_edit_form'])) {
      $summary[] = $this->t('Link to the edit form of the referenced entity');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    assert($items instanceof EntityReferenceFieldItemListInterface);
    $referenced_entities = $items->referencedEntities();
    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];

    if (isset($referenced_entities[$delta])) {
      if ($this->getSetting('link_to_entity') && !$referenced_entities[$delta]->isNew()) {
        $entity_label = $referenced_entities[$delta]->toLink()->toString();
      }
      else {
        $entity_label = $referenced_entities[$delta]->label();
      }
      $id_prefix = implode('-', array_merge($parents, [$field_name, $delta]));

      $element += [
        '#type' => 'container',
        '#attributes' => ['class' => ['form--inline']],
        'target_id' => [
          '#type' => 'item',
          '#markup' => $entity_label,
          '#default_value' => !$referenced_entities[$delta]->isNew() ? $referenced_entities[$delta]->id() : NULL,
        ],
        'entity' => [
          '#type' => 'value',
          '#default_value' => $referenced_entities[$delta],
        ],
        'edit' => $referenced_entities[$delta]->toLink($this->t('Edit'), 'edit-form', ['query' => \Drupal::destination()->getAsArray()])->toRenderable() + [
          '#attributes' => ['class' => ['form-item']],
          '#access' => (bool) $this->getSetting('link_to_edit_form'),
        ],
        'remove' => [
          '#type' => 'submit',
          '#name' => strtr($id_prefix, '-', '_') . '_remove',
          '#value' => $this->t('Remove'),
          '#attributes' => ['class' => ['remove-item-submit', 'align-right']],
          '#submit' => [[get_class($this), 'removeSubmit']],
          '#ajax' => [
            'callback' => [get_class($this), 'getWidgetElementAjax'],
            'wrapper' => $this->getWrapperId(),
            'effect' => 'fade',
          ],
        ],
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $parents = $form['#parents'];

    // Assign a unique identifier to each widget.
    $id_prefix = implode('-', array_merge($parents, [$field_name]));
    $wrapper_id = Html::getUniqueId($id_prefix . '-add-more-wrapper');
    $this->setWrapperId($wrapper_id);

    // Load the items for form rebuilds from the field state as they might not
    // be in $form_state->getValues() because of validation limitations. Also,
    // they are only passed in as $items when editing existing entities.
    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    if (isset($field_state['items'])) {
      $items->setValue($field_state['items']);
    }

    // Lower the 'items_count' field state property in order to prevent the
    // parent implementation to append an extra empty item.
    if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $field_state['items_count'] = (count($items) > 1) ? count($items) - 1 : 0;
      static::setWidgetState($parents, $field_name, $form_state, $field_state);
    }

    $elements = parent::formMultipleElements($items, $form, $form_state);

    if ($elements) {
      if (isset($elements['add_more'])) {
        // Update the HTML wrapper ID with the one generated by us.
        $elements['#prefix'] = '<div id="' . $this->getWrapperId() . '">';

        $add_more_button = $elements['add_more'];
        $add_more_button['#value'] = $this->t('Add item');
        $add_more_button['#ajax']['callback'] = [get_class($this), 'getWidgetElementAjax'];
        $add_more_button['#ajax']['wrapper'] = $this->getWrapperId();

        $elements['add_more'] = [
          '#type' => 'container',
          '#tree' => TRUE,
          '#attributes' => ['class' => ['form--inline']],
          'new_item' => parent::formElement($items, -1, [], $form, $form_state),
          'submit' => $add_more_button,
        ];
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function getWidgetElementAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go two levels up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));

    // Ensure the widget allows adding additional items.
    if ($element['#cardinality'] != FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      return NULL;
    }

    // Add a DIV around the delta receiving the Ajax effect.
    $delta = $element['#max_delta'];
    $element[$delta]['#prefix'] = '<div class="ajax-new-content">' . (isset($element[$delta]['#prefix']) ? $element[$delta]['#prefix'] : '');
    $element[$delta]['#suffix'] = (isset($element[$delta]['#suffix']) ? $element[$delta]['#suffix'] : '') . '</div>';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state) {
    // During the form rebuild, formElement() will create field item widget
    // elements using re-indexed deltas, so clear out FormState::$input to
    // avoid a mismatch between old and new deltas. The rebuilt elements will
    // have #default_value set appropriately for the current state of the field,
    // so nothing is lost in doing this.
    $button = $form_state->getTriggeringElement();
    $parents = array_slice($button['#parents'], 0, -2);
    NestedArray::setValue($form_state->getUserInput(), $parents, NULL);

    // Go two levels up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));
    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    $submitted_values = NestedArray::getValue($form_state->getValues(), array_slice($button['#parents'], 0, -2));

    // Check submitted values for empty items.
    $new_values = [];
    foreach ($submitted_values as $delta => $submitted_value) {
      if ($delta !== 'add_more' && (isset($submitted_value['target_id']) || isset($submitted_value['entity']))) {
        $new_values[] = $submitted_value;
      }

      if ($delta === 'add_more' && (isset($submitted_value['new_item']['target_id']) || isset($submitted_value['new_item']['entity']))) {
        $new_values[] = $submitted_value['new_item'];
      }
    }

    // Re-index deltas after removing empty items.
    $submitted_values = array_values($new_values);

    // Update form_state values.
    NestedArray::setValue($form_state->getValues(), array_slice($button['#parents'], 0, -2), $submitted_values);

    // Update items.
    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    $field_state['items'] = $submitted_values;
    static::setWidgetState($parents, $field_name, $form_state, $field_state);

    $form_state->setRebuild();
  }

  /**
   * Submission handler for the "Remove" button.
   */
  public static function removeSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the single field item element container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));

    $form_state->setValueForElement($element, ['target_id' => NULL, 'entity' => NULL]);

    // Call the generic submit handler which takes care of removing the item.
    static::addMoreSubmit($form, $form_state);
  }

  /**
   * Sets the unique HTML ID of the widget's wrapping element.
   *
   * @param string $wrapperId
   *   The unique HTML ID.
   */
  public function setWrapperId($wrapperId) {
    if (!$this->wrapperId) {
      $this->wrapperId = $wrapperId;
    }
  }

  /**
   * Gets the unique HTML ID of the widget's wrapping element.
   *
   * @return string
   *   The unique HTML ID.
   */
  public function getWrapperId() {
    return $this->wrapperId;
  }

}
