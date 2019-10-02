<?php

namespace Drupal\wxt_library\Plugin\CKEditorPlugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\ckeditor\CKEditorPluginCssInterface;

/**
 * Defines the "wxtlibraries" plugin.
 *
 * @CKEditorPlugin(
 *   id = "wxtlibraries",
 *   label = @Translation("WxT Libraries"),
 *   module = "wxt_library"
 * )
 */
class Libraries extends PluginBase implements CKEditorPluginInterface, CKEditorPluginContextualInterface, CKEditorPluginCssInterface {

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'wxt_library') . '/js/plugins/wxtlibraries/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor) {
    $config = \Drupal::config('wxt_library.settings');
    $wxt_active = $config->get('wxt.theme');
    $wxt_active = _wxt_library_get_path($wxt_active, TRUE);

    return [
      $wxt_active . '/css/theme.min.css',
      $wxt_active . '/css/wet-boew.min.css',
      drupal_get_path('module', 'wxt_library') . '/css/plugins/wxtlibraries/ckeditor.wxtlibraries.css',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(Editor $editor) {
    return TRUE;
  }

}
