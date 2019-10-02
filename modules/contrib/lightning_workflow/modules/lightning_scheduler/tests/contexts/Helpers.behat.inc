<?php

namespace Acquia\LightningExtension\Context;

use Drupal\Core\Url;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains miscellaneous helpful step definitions.
 *
 * @todo Find a way to override the Drupal Extension's "I run cron" step, then
 * remove this context.
 */
final class Helpers extends DrupalSubContextBase {

  /**
   * Runs cron by invoking the /cron URL.
   *
   * @When I run cron over HTTP
   */
  public function cron() {
    $url = Url::fromRoute('system.cron', [
      'key' => \Drupal::state()->get('system.cron_key'),
    ]);
    $url = $url->toString();
    $url = $this->locatePath($url);

    \Drupal::httpClient()->get($url);
  }

}
