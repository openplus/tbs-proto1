<?php

namespace Drupal\openplus_migrate\Util;

class ConfigUtil {
  const CONFIG_NAME = 'openplus_migrate.settings';
  const HARVESTER_API_URL_SETTING = 'harvester_api_url';


  private static function GetConfig() {
    return \Drupal::config(self::CONFIG_NAME);
  }


  /**
   * @return string
   */
  public static function GetHarvesterBaseUrl() {
    $defaultUrl = 'https://gccloud.ca:3000/nodejs/export'; // To be used only if settings form has not been submitted yet.
    $config     = self::GetConfig();
    $url        = $config->get(self::HARVESTER_API_URL_SETTING) ?? $defaultUrl;
    return rtrim($url, '/') . '/';
  }
}
