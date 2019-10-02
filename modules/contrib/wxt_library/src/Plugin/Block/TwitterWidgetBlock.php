<?php

namespace Drupal\wxt_library\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'TwitterWidgetBlock' block.
 *
 * @Block(
 *  id = "twitter_widget_block",
 *  admin_label = @Translation("Twitter widget block"),
 * )
 */
class TwitterWidgetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'twitter_settings' => [
        'widget_type' => '',
        'username' => '',
        'search_query' => '',
        'widget_id' => '',
        'tweet_limit' => '',
        'widget_height' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['twitter_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configure Twitter Widget'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => $this->t('Configure Twitter Widget.'),
    ];

    $form['twitter_settings']['widget_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Twitter widget type'),
      '#description' => $this->t("Shows the user's most recent tweets."),
      '#options' => [
        'profile' => $this->t('Profile'),
        'search' => $this->t('Search'),
      ],
      '#default_value' => empty($this->configuration['widget_type']) ? '' : $this->configuration['widget_type'],
      '#required' => TRUE,
    ];

    $form['twitter_settings']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter user name: @'),
      '#size' => 25,
      '#maxlength' => 25,
      '#default_value' => empty($this->configuration['username']) ? '' : $this->configuration['username'],
      '#states' => [
        'visible' => [
          ':input[name="twitter_settings[widget_type]"]' => ['value' => 'profile'],
        ],
      ],
    ];

    $form['twitter_settings']['search_query'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter search query'),
      '#size' => 25,
      '#maxlength' => 25,
      '#default_value' => empty($this->configuration['search_query']) ? '' : $this->configuration['search_query'],
      '#states' => [
        'visible' => [
          ':input[name="twitter_settings[widget_type]"]' => ['value' => 'search'],
        ],
      ],
    ];

    $form['twitter_settings']['widget_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twitter widget ID'),
      '#description' => $this->t('Widget ID provided by twitter. The ID is required for the widget to work and must be created on https://twitter.com/settings/widgets'),
      '#size' => 80,
      '#maxlength' => 80,
      '#default_value' => empty($this->configuration['widget_id']) ? '' : $this->configuration['widget_id'],
      '#required' => TRUE,
    ];

    $form['twitter_settings']['tweet_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Tweet limit'),
      '#default_value' => empty($this->configuration['tweet_limit']) ? '' : $this->configuration['tweet_limit'],
      '#options' => ['' => array_combine(range(1, 20), range(1, 20))],
      '#description' => $this->t('Fix the size of a timeline to a preset number of Tweets between 1 and 20. The timeline will render the specified number of Tweets from the timeline, expanding the height of the widget to display all Tweets without scrolling.'),
    ];

    $form['twitter_settings']['widget_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget Height'),
      '#default_value' => empty($this->configuration['widget_height']) ? '' : $this->configuration['widget_height'],
      '#description' => $this->t('This is where you would select the height of your twitter widget. If widget height is selected, your tweet limit will NOT work.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $conf = $form_state->getValue('twitter_settings');
    $this->configuration['widget_type'] = $conf['widget_type'];
    $this->configuration['username'] = $conf['username'];
    $this->configuration['search_query'] = $conf['search_query'];
    $this->configuration['widget_id'] = $conf['widget_id'];
    $this->configuration['tweet_limit'] = $conf['tweet_limit'];
    $this->configuration['widget_height'] = $conf['widget_height'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $widget_id = $this->configuration['widget_id'];
    $widget_type = $this->configuration['widget_type'];
    $username = $this->configuration['username'];
    $search_query = $this->configuration['search_query'];
    $tweet_limit = $this->configuration['tweet_limit'];
    $widget_height = $this->configuration['widget_height'];

    // Twitter method.
    $url = "https://twitter.com/";
    switch ($widget_type) {
      case 'profile':
        $url .= $username;
        break;

      case 'search':
        $url .= 'search?q=' . $search_query;
        break;

    }

    // Rendered markup.
    $markup = '<section><div class="wb-twitter">';
    $markup .= '<a class="twitter-timeline" height="' . $widget_height . '" href="' . $url . '" data-widget-id="' . $widget_id . '" ';
    $markup .= (empty($widget_height)) ? $tweet_limit : '';
    $markup .= '>Tweets</a>';
    $markup .= '</div></section>';

    $build['share_widget_block']['#markup'] = $markup;
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make cacheable in https://www.drupal.org/node/2483181
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
