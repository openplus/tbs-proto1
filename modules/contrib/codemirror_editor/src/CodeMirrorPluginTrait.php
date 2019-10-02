<?php

namespace Drupal\codemirror_editor;

/**
 * Provides a helper methods to for CodeMirror plugins.
 */
trait CodeMirrorPluginTrait {

  /**
   * Returns the default settings for CodeMirror plugin.
   *
   * @return array
   *   A list of default settings, keyed by the setting name.
   */
  protected static function getDefaultCodeMirrorSettings() {
    return [
      'mode' => 'text/html',
      'toolbar' => TRUE,
      'lineNumbers' => FALSE,
      'foldGutter' => FALSE,
      'autoCloseTags' => TRUE,
      'styleActiveLine' => FALSE,
    ];
  }

  /**
   * Returns a form to configure settings for the CodeMirror plugin.
   *
   * @param array $settings
   *   The plugin settings.
   * @param array $keys
   *   If provided only specified form elements will be rendered.
   *
   * @return array
   *   The form definition for the plugin settings.
   */
  protected static function buildCodeMirrorSettingsForm(array $settings, array $keys = NULL) {

    $form = [];

    if (!$keys || in_array('mode', $keys)) {
      $definitions = \Drupal::service('plugin.manager.codemirror_mode')->getDefinitions();
      $enabled_modes = \Drupal::config('codemirror_editor.settings')->get('language_modes');
      $options = [];
      foreach ($definitions as $mode => $definition) {
        if (in_array($mode, $enabled_modes)) {
          ksort($definition['mime_types']);
          foreach ($definition['mime_types'] as $mime_type) {
            $options[$definition['label']][$mime_type] = $mime_type;
          }
        }
      }
      $form['mode'] = [
        '#type' => 'select',
        '#title' => t('Language mode'),
        '#default_value' => $settings['mode'],
        '#options' => $options,
      ];
    }

    if (!$keys || in_array('toolbar', $keys)) {
      $form['toolbar'] = [
        '#title' => t('Load toolbar'),
        '#type' => 'checkbox',
        '#default_value' => $settings['toolbar'],
      ];
    }

    if (!$keys || in_array('lineNumbers', $keys)) {
      $form['lineNumbers'] = [
        '#title' => t('Line numbers'),
        '#type' => 'checkbox',
        '#default_value' => $settings['lineNumbers'],
      ];
    }

    if (!$keys || in_array('foldGutter', $keys)) {
      $form['foldGutter'] = [
        '#title' => t('Fold gutter'),
        '#type' => 'checkbox',
        '#default_value' => $settings['foldGutter'],
      ];
    }

    if (!$keys || in_array('autoCloseTags', $keys)) {
      $form['autoCloseTags'] = [
        '#title' => t('Auto close tags'),
        '#type' => 'checkbox',
        '#default_value' => $settings['autoCloseTags'],
      ];
    }

    if (!$keys  || in_array('styleActiveLine', $keys)) {
      $form['styleActiveLine'] = [
        '#title' => t('Style active line'),
        '#type' => 'checkbox',
        '#default_value' => $settings['styleActiveLine'],
      ];
    }

    return $form;
  }

  /**
   * Returns formatted boolean setting value.
   *
   * @param string $key
   *   Plugin setting key to format.
   *
   * @return string
   *   Format settings value.
   */
  protected function formatBoolean($key) {
    return $this->settings[$key] ? $this->t('Yes') : $this->t('No');
  }

  /**
   * Normalizes language mode.
   *
   * @param string $mode
   *   Language mode to normalize.
   *
   * @return string
   *   Normalized language mode.
   */
  protected static function normalizeMode($mode) {
    return \Drupal::service('plugin.manager.codemirror_mode')->normalizeMode($mode);
  }

}
