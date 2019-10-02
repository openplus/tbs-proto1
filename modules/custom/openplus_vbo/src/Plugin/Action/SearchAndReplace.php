<?php

namespace Drupal\openplus_vbo\Plugin\Action;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;


/**
 * Search and replace vbo action.
 *
 * @Action(
 *   id = "search_and_replace_action",
 *   label = @Translation("Search and replace"),
 *   type = "node",
 *   confirm = FALSE,
 *   requirements = {
 *     "_permission" = "administer nodes",
 *   },
 * )
 */

class SearchAndReplace extends ViewsBulkOperationsActionBase implements ViewsBulkOperationsPreconfigurationInterface, PluginFormInterface {

  //use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {

/*
    $form['example'] = [
      '#title' => $this->t('Example'),
      '#type' => 'text',
      '#default_value' => isset($values['example']) ? $values['example'] : '',
    ];
*/

    return $form;
  }

  /**
   * Configuration form builder.
   *
   * If this method has implementation, the action is
   * considered to be configurable.
   *
   * @param array $form
   *   Form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['action_type'] = [
      '#title' => $this->t('Replace using'),
      '#type' => 'radios',
      '#options' => array($this->t('Text match'), $this->t('Regular expression')),
      '#default_value' => isset($values['action_type']) ? $values['action_type'] : 0,
    ];

    $form['search_string'] = [
      '#title' => $this->t('Search string'),
      '#type' => 'textfield',
      '#default_value' => isset($values['search_string']) ? $values['search_string'] : '',
    ];

    $form['replace_string'] = [
      '#title' => $this->t('Replacement string'),
      '#type' => 'textfield',
      '#default_value' => isset($values['replace_string']) ? $values['replace_string'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $config = $this->configuration;

    if (!isset($this->context['sandbox']['counter'])) {
      $this->context['sandbox']['counter'] = 0;
    }

    $body = $entity->get('body')->first()->getValue();

    if ($config['action_type']) {
      $matches = array();
      $new_body = preg_replace($config['search_string'], $config['replace_string'], $body['value'], -1,  $hits);
    }
    else {
      $new_body = str_replace($config['search_string'], $config['replace_string'], $body['value'], $hits);
    }

    if ($hits > 0) {
      $entity->body->setValue(['value' => $new_body, 'format' => 'rich_text']);
      $entity->save();
      $this->context['sandbox']['counter']++;
    }

    // Don't return anything for a default completion message, otherwise return translatable markup.
    //return $this->t('Update completed');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {

    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    return TRUE;
  }

}
