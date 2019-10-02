<?php

namespace Drupal\ckeditor_templates_ui\Plugin\CKEditorPlugin;

use Drupal\ckeditor_templates\Plugin\CKEditorPlugin\CkeditorTemplates;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Templates" plugin.
 *
 * @CKEditorPlugin(
 *   id = "templates",
 *   label = @Translation("Templates")
 * )
 */
class CkeditorTemplatesUi extends CkeditorTemplates {

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    $config = [];
    $settings = $editor->getSettings();
    // Set replace content default value if set.
    if (isset($settings['plugins']['templates']['replace_content'])) {
      $config['templates_replaceContent'] = $settings['plugins']['templates']['replace_content'];
    }
    // Set template files default value if set.
    if (isset($settings['plugins']['templates']['template_path']) && !empty($settings['plugins']['templates']['template_path'])) {
      $config['templates_files'] = [$settings['plugins']['templates']['template_path']];
    }
    else {
      // Use templates plugin default file.
      $config['templates_files'] = $this->getTemplatesDefaultPath();
    }
    return $config;
  }

  /**
   * Generate the path to the template file.
   *
   * The file will be picked from :
   * - the module js folder.
   *
   * @return array
   *   List of path to the template file.
   */
  private function getTemplatesDefaultPath() {
    return ['/' . drupal_get_path('module', 'ckeditor_templates_ui') . '/js/ckeditor_templates.js'];
  }

}
