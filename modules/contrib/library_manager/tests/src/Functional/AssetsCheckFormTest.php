<?php

namespace Drupal\Tests\library_manager\Functional;

/**
 * Tests assets check form.
 *
 * @group library_manager
 */
class AssetsCheckFormTest extends TestBase {

  /**
   * Test callback.
   */
  public function testAssetsCheckForm() {
    $this->drupalGet('admin/reports');
    $this->assertXpath('//dd[text() = "Check installed library assets."]');
    $this->clickLink('Library assets');
    $this->assertPageTitle('Library assets');
    $this->assertXpath('//div[normalize-space() = "Last check: never."]');
    $this->drupalPostForm(NULL, [], 'Check assets');
    $assert_session = $this->assertSession();
    $assert_session->responseMatches('#Could not load .*library_manager/tests/library_manager_test/js/example.js#');
    $assert_session->responseMatches('#Could not load .*library_manager/tests/library_manager_test/css/example.css#');
    $assert_session->responseMatches('#Loaded \d{1,3} of \d{1,3}\.#');
    $assert_session->responseMatches('#Last check: \d seconds ago\.#');
  }

}
