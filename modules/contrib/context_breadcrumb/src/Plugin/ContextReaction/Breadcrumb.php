<?php

namespace Drupal\context_breadcrumb\Plugin\ContextReaction;

use Drupal\context\ContextReactionPluginBase;
use Drupal\context_breadcrumb\Breadcrumb\ContextBreadcrumbBuilder;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a content reaction that adds breadcrumb to page.
 *
 * @ContextReaction(
 *   id = "context_breadcrumb",
 *   label = @Translation("Breadcrumb")
 * )
 */
class Breadcrumb extends ContextReactionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $permission = [
      'breadcrumbs' => [],
    ];

    return parent::defaultConfiguration() + $permission;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Context breadcrumb');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    return $this->getConfiguration()['breadcrumbs'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $breadcrumbs = $this->getConfiguration()['breadcrumbs'];
    $form['breadcrumbs'] = [
      '#type' => 'table',
      '#header' => [$this->t('Title'), $this->t('Url'), $this->t('Token')],
      '#rows' => [],
    ];

    for ($i = 0; $i <= 8; $i++) {
      $form['breadcrumbs'][$i]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($breadcrumbs[$i]['title']) ? $breadcrumbs[$i]['title'] : '',
      ];
      $form['breadcrumbs'][$i]['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($breadcrumbs[$i]['url']) ? $breadcrumbs[$i]['url'] : '',
      ];

      $form['breadcrumbs'][$i]['token'] = [
        '#type' => 'select',
        '#required' => FALSE,
        '#title' => $this->t('Token'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($breadcrumbs[$i]['token']) ? 1 : '',
        '#empty_value' => '',
        '#options' => [
          '' => $this->t('None'),
          1 => $this->t('Yes'),
        ],
      ];
    }
    if (\Drupal::service('module_handler')->moduleExists("token")) {
      $token_tree = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['node', 'user', 'term', 'vocabulary'],
      ];

      $rendered_token_tree = \Drupal::service('renderer')->render($token_tree);
      $form['description']['#type'] = 'item';
      $form['description']['#description'] = t('This field supports tokens. @browse_tokens_link', [
        '@browse_tokens_link' => $rendered_token_tree,
      ]);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    foreach ($form_state->getValue('breadcrumbs') as $i => $breadcrumb) {
      if (!empty($breadcrumb['title']) && empty($breadcrumb['url'])) {
        $form_state->setErrorByName('breadcrumbs][' . $i . '][url', $this->t('@name field is required.', ['@name' => 'Url']));
      }
      if (empty($breadcrumb['title']) && !empty($breadcrumb['url'])) {
        $form_state->setErrorByName('breadcrumbs][' . $i . '][token', $this->t('@name field is required.', ['@name' => 'Token']));
      }

      if (!empty($breadcrumb['title']) && !empty($breadcrumb['url'])) {
        if (ContextBreadcrumbBuilder::isToken($breadcrumb['url']) && empty($breadcrumb['token'])) {
          $form_state->setErrorByName('breadcrumbs][' . $i . '][token', $this->t('The url using token, please select token option.'));
        }
        if (ContextBreadcrumbBuilder::isToken($breadcrumb['title']) && empty($breadcrumb['token'])) {
          $form_state->setErrorByName('breadcrumbs][' . $i . '][token', $this->t('The title using token, please select token option.'));
        }
        if (!ContextBreadcrumbBuilder::isToken($breadcrumb['url']) && !in_array($breadcrumb['url'], ['<front>', '<nolink>']) && strpos($breadcrumb['url'], 'http://') === FALSE && strpos($breadcrumb['url'], 'https://') === FALSE && $breadcrumb['url'][0] !== '/') {
        //if (!ContextBreadcrumbBuilder::isToken($breadcrumb['url']) && !in_array($breadcrumb['url'], ['<front>', '<nolink>']) && $breadcrumb['url'][0] !== '/') {
          $form_state->setErrorByName('breadcrumbs][' . $i . '][url', $this->t('The url path has to start with a slash.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'breadcrumbs' => $form_state->getValue('breadcrumbs'),
    ]);
    Cache::invalidateTags(['context:breadcrumb']);
  }

}
