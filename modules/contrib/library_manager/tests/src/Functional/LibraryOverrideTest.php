<?php

namespace Drupal\Tests\library_manager\Functional;

/**
 * Tests library overriding.
 *
 * @group library_manager
 */
class LibraryOverrideTest extends TestBase {

  /**
   * Test callback.
   */
  public function testOverriding() {
    $assert_session = $this->assertSession();

    // jQuery is loaded because alpha library depends upon it.
    $this->drupalGet('<front>');
    $assert_session->responseContains('/core/assets/vendor/jquery/jquery.min.js');
    $assert_session->responseNotContains('/sites/default/files/libraries/custom/jquery_overridden/assets/vendor/jquery/jquery.min.js');

    // Override core/jquery library.
    $this->drupalGet('admin/structure/library');
    $this->clickLinkInRow('core/jquery', 'Create definition');
    $this->drupalPostForm(NULL, ['id' => 'jquery_overridden'], 'Create');

    // Make sure that the jQuery library is replaced.
    $this->drupalGet('<front>');
    $assert_session->responseNotContains('/core/assets/vendor/jquery/jquery.min.js');
    $assert_session->responseContains('/sites/default/files/libraries/custom/jquery_overridden/assets/vendor/jquery/jquery.min.js');
  }

}
