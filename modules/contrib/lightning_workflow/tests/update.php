<?php

// Forcibly uninstall Lightning Dev switch the installation profile from
// Standard to Minimal, and delete defunct config objects.

Drupal::configFactory()
  ->getEditable('core.extension')
  ->clear('module.lightning_dev')
  ->clear('module.standard')
  ->set('module.minimal', 1000)
  ->set('profile', 'minimal')
  ->save();

Drupal::keyValue('system.schema')->deleteMultiple(['lightning_dev']);

Drupal::configFactory()
  ->getEditable('entity_browser.browser.media_browser')
  ->delete();

Drupal::configFactory()->getEditable('lightning_api.settings')->delete();
Drupal::configFactory()->getEditable('media.type.tweet')->delete();

Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();

$node_type = entity_load('node_type', 'page');
if ($node_type) {
  $node_type->delete();
}

user_role_revoke_permissions('authenticated', ['use text format basic_html']);
