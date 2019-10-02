<?php

namespace Drupal\Tests\library_manager\Functional;

use Drupal\codemirror_editor\CodeMirrorLibraryBuilder;

/**
 * Tests CodeMirror library assets.
 *
 * @group library_manager
 */
class CodeMirrorAssetsTest extends TestBase {

  /**
   * Test callback.
   */
  public function testAssets() {

    $cm_version = CodeMirrorLibraryBuilder::CODEMIRROR_VERSION;

    $this->drupalGet('admin/structure/library/definition/alpha/js/add');
    $js_xpath = '//script[@src ="https://cdnjs.cloudflare.com/ajax/libs/codemirror/%s/%s"]';
    $this->assertXpath(sprintf($js_xpath, $cm_version, 'codemirror.min.js'));
    $this->assertXpath(sprintf($js_xpath, $cm_version, 'mode/javascript/javascript.min.js'));
    $this->assertXpath(sprintf($js_xpath, $cm_version, 'mode/css/css.min.js'));
    $this->assertXpath(sprintf($js_xpath, $cm_version, 'mode/yaml/yaml.min.js'));
    $this->assertXpath(sprintf($js_xpath, $cm_version, 'addon/fold/foldcode.min.js'));
    $this->assertXpath(sprintf($js_xpath, $cm_version, 'addon/display/fullscreen.min.js'));

    $css_xpath = '//head/link[@rel = "stylesheet" and @href = "https://cdnjs.cloudflare.com/ajax/libs/codemirror/%s/%s"]';
    $this->assertXpath(sprintf($css_xpath, $cm_version, 'codemirror.min.css'));
    $this->assertXpath(sprintf($css_xpath, $cm_version, 'addon/display/fullscreen.css'));
  }

}
