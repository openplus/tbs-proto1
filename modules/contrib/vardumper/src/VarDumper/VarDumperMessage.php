<?php

namespace Drupal\vardumper\VarDumper;

use Drupal\Core\Render\Markup;

/**
 *
 */
class VarDumperMessage extends VarDumperDebug {

  /**
   * {@inheritDoc}.
   */
  public function dump($var, $name = '') {
    if (!$this->hasPermission()) {
      return;
    }
    $html = $this->getHeaders($name, $this->getDebugInformation()) . $this->getDebug($var);

    drupal_set_message(Markup::create($html), 'status', FALSE);
  }

}
