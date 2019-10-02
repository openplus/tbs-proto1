<?php

namespace Drupal\wxt_library\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ShareWidgetBlock' block.
 *
 * @Block(
 *  id = "share_widget_block",
 *  admin_label = @Translation("Share widget block"),
 * )
 */
class ShareWidgetBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
      'share_settings' => [
        'share_widget' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['share_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configure Share Widget'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => $this->t('Configure Share Widget.'),
    ];

    $form['share_settings']['share_widget'] = [
      '#type' => 'select',
      '#title' => $this->t('Configure Share Widget'),
      '#description' => $this->t('Configure Share Widget.'),
      '#options' => [
        'bitly' => $this->t('bit.ly'),
        'blogger' => $this->t('blogger'),
        'delicious' => $this->t('delicious'),
        'digg' => $this->t('digg'),
        'diigo' => $this->t('diigo'),
        'email' => $this->t('email'),
        'facebook' => $this->t('facebook'),
        'gmail' => $this->t('gmail'),
        'googleplus' => $this->t('googleplus'),
        'linkedin' => $this->t('linkedin'),
        'myspace' => $this->t('myspace'),
        'pinterest' => $this->t('pinterest'),
        'reddit' => $this->t('reddit'),
        'stumbleupon' => $this->t('stumbleupon'),
        'tumblr' => $this->t('tumblr'),
        'twitter' => $this->t('twitter'),
        'yahoomail' => $this->t('yahoomail'),
      ],
      '#default_value' => empty($this->configuration['share_widget']) ? '' : $this->configuration['share_widget'],
      '#required' => TRUE,
      '#multiple' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $share_conf = $form_state->getValue('share_settings');
    $this->configuration['share_widget'] = $share_conf['share_widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $widget = $this->configuration['share_widget'];

    // JSON params.
    $data = [
      'filter' => array_keys($widget),
      'pnlId' => 'pnl1',
      'lnkClass' => 'btn btn-default',
    ];
    $data = (array) json_encode($data);
    $markup = '<div class="wb-share mrgn-bttm-sm pull-right" data-wb-share=\'' . $data[0] . '\'></div>';
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
