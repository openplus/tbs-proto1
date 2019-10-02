<?php

namespace Drupal\config_rewrite;

/**
 * Provides an interface for the ConfigRewriter.
 */
interface ConfigRewriterInterface {

  /**
   * Extension sub-directory containing default configuration for installation.
   */
  const CONFIG_REWRITE_DIRECTORY = 'config/rewrite';

  /**
   * Rewrites module config.
   *
   * @param $module
   *   The name of a module (without the .module extension).
   */
  public function rewriteModuleConfig($module);

  /**
   * Returns rewritten configuration.
   *
   * @param array $original_config
   *   The original configuration array to rewrite.
   * @param array $rewrite
   *   An array of configuration rewrites.
   *
   * @return array
   *   The rewritten config.
   */
  public function rewriteConfig($original_config, $rewrite);

}
