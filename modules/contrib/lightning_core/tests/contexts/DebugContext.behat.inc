<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Contains step definitions to assist with debugging tests.
 */
final class DebugContext extends DrupalSubContextBase {

  /**
   * The number of screenshots that have been taken during the scenario.
   *
   * @var int
   */
  private $screenshotCount = 0;

  /**
   * Waits a while, for debugging.
   *
   * @param int $seconds
   *   How long to wait.
   *
   * @When I wait :seconds second(s)
   */
  public function wait($seconds) {
    sleep($seconds);
  }

  /**
   * Dumps the current page HTML.
   *
   * @When I dump the HTML
   */
  public function dumpHTML() {
    print_r($this->getSession()->getPage()->getContent());
  }

  /**
   * Takes a screenshot.
   *
   * @param string $filename
   *   (optional) Ignored. The filename is based on a counter and prefixed with
   *   the name of the Mink browser.
   * @param null $filepath
   *   (optional) Ignored. The screenshot is saved in the directory above the
   *   Drupal root.
   *
   * @When I take a screenshot
   */
  public function saveScreenshot($filename = NULL, $filepath = NULL) {
    $filename = sprintf('%s_%d.png', $this->getMinkParameter('browser_name'), ++$this->screenshotCount);
    $filepath = \Drupal::root() . '/../';
    parent::saveScreenshot($filename, $filepath);
  }

}
