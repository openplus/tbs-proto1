<?php

namespace Drupal\moderation_note;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\moderation_note\Ajax\AddModerationNoteCommand;
use Drupal\moderation_note\Ajax\ReplyModerationNoteCommand;

/**
 * Form handler for the moderation_note edit forms.
 */
class ModerationNoteForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\moderation_note\Entity\ModerationNote $note */
    $note = $this->entity;

    // Wrap our form so that our submit callback can re-render the form.
    $form_id = $this->getOperation() === 'edit' ? $note->id() : $this->getOperation();
    $form['#prefix'] = '<div class="moderation-note-form-wrapper" data-moderation-note-form-id="' . $form_id . '">';
    $form['#suffix'] = '</div>';

    if ($this->getOperation() !== 'reply') {
      $form['assignee'] = [
        '#type' => 'entity_autocomplete',
        '#title' => 'Assignee',
        '#selection_handler' => 'moderation_note:user',
        '#target_type' => 'user',
        '#placeholder' => $this->t('None'),
        '#required' => FALSE,
        '#default_value' => $note->getAssignee(),
      ];
    }

    $form['text'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#default_value' => $note->getText(),
      '#required_error' => $this->t('Note text is required'),
    ];

    $form['quote'] = [
      '#type' => 'textarea',
      '#attributes' => [
        'class' => ['visually-hidden', 'field-moderation-note-quote'],
      ],
      '#resizable' => 'none',
      '#default_value' => $note->getQuote(),
    ];

    $form['quote_offset'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'class' => ['visually-hidden', 'field-moderation-note-quote-offset'],
      ],
      '#default_value' => $note->getQuoteOffset(),
    ];

    if ($this->getOperation() === 'reply' || $this->entity->hasParent()) {
      $form['#attributes']['class'][] = 'moderation-note-form-reply';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['submit'] = [
      '#type' => 'submit',
      '#value' => $this->getOperation() === 'reply' ? $this->t('Reply') : $this->t('Save'),
      '#ajax' => [
        'callback' => '::submitFormCallback',
        'method' => 'replace',
        'disable-refocus' => TRUE,
        'progress' => [
          'type' => 'fullscreen',
          'message' => NULL,
        ],
      ],
    ];

    if ($this->getOperation() !== 'reply') {
      $actions['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        '#executes_submit_callback' => FALSE,
        '#ajax' => [
          'callback' => '::cancelForm',
          'method' => 'replace',
          'disable-refocus' => TRUE,
          'progress' => [
            'type' => 'fullscreen',
            'message' => NULL,
          ],
        ],
      ];
    }

    return $actions;
  }

  /**
   * Submission callback when the cancel button is clicked.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response which cancels the form.
   */
  public function cancelForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($this->getOperation() === 'create') {
      $command = new CloseDialogCommand('#drupal-off-canvas');
    }
    else {
      /** @var \Drupal\moderation_note\ModerationNoteInterface $note */
      $note = $this->entity;
      $selector = '[data-moderation-note-form-id="' . $note->id() . '"]';
      $content = $this->entityTypeManager->getViewBuilder('moderation_note')->view($note);
      $command = new ReplaceCommand($selector, $content);
    }

    $response->addCommand($command);

    return $response;
  }

  /**
   * Submission callback when the Save/Reply button is clicked.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response which adds the note.
   */
  public function submitFormCallback(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\moderation_note\Entity\ModerationNote $note */
    $note = $this->entity;

    $form_id = $this->getOperation() === 'edit' ? $note->id() : $this->getOperation();
    $selector = '[data-moderation-note-form-id="' . $form_id . '"]';

    // If the form has errors, return the contents of the form.
    // @todo Use trait that comes with https://www.drupal.org/node/2896535
    if ($form_state->hasAnyErrors()) {
      $response = new AjaxResponse();
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -1000,
      ];
      $command = new ReplaceCommand($selector, $form);
      $response->addCommand($command);
      return $response;
    }

    $response = new AjaxResponse();

    if ($this->getOperation() === 'create') {
      $command = new AddModerationNoteCommand($note);
      $response->addCommand($command);
      $command = new CloseDialogCommand('#drupal-off-canvas');
    }
    else {
      $content = $this->entityTypeManager->getViewBuilder('moderation_note')->view($note);
      $command = new ReplaceCommand($selector, $content);
    }

    $response->addCommand($command);

    if ($this->getOperation() === 'reply') {
      $command = new ReplyModerationNoteCommand($note->getParent());
      $response->addCommand($command);
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    parent::save($form, $form_state);
  }

}
