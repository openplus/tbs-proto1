<?php

namespace Drupal\Tests\library_manager\Functional;

/**
 * Tests routes access.
 *
 * @group library_manager
 */
class AccessTest extends TestBase {

  /**
   * Test callback.
   */
  public function testAccess() {

    $paths = [
      'admin/structure/library/settings',
      'admin/structure/library',
      'admin/structure/library/autocomplete',
      'admin/structure/library/library/library_manager/alpha',
      'admin/structure/library/library/library_manager/alpha/export',
      'admin/structure/library/definition',
      'admin/structure/library/definition/add',
      'admin/structure/library/definition/alpha/edit',
      'admin/structure/library/definition/alpha/delete',
      'admin/structure/library/definition/alpha/js/add',
      'admin/structure/library/definition/alpha/js/1/edit',
      'admin/structure/library/definition/alpha/js/1/delete',
      'admin/structure/library/definition/alpha/css/add',
      'admin/structure/library/definition/alpha/css/1/edit',
      'admin/structure/library/definition/alpha/css/1/delete',
      'admin/reports/libraries',
    ];

    $assert_session = $this->assertSession();

    foreach ($paths as $path) {
      $this->drupalGet($path);
      $assert_session->statusCodeEquals(200);
    }

    $non_privileged_user = $this->drupalCreateUser();
    $this->drupalLogin($non_privileged_user);

    foreach ($paths as $path) {
      $this->drupalGet($path);
      $assert_session->statusCodeEquals(403);
    }

  }

}
