<?php

namespace Drupal\Tests\moderation_dashboard\Functional;

/**
 * Tests redirect on login configuration.
 *
 * @group moderation_dashboard
 */
class RedirectOnLoginTest extends ModerationDashboardTestBase {

  /**
   * Tests enabled redirect on login.
   */
  public function testEnabled() {
    // Redirect is enabled by default.
    $this->assertSame(TRUE, $this->config('moderation_dashboard.settings')->get('redirect_on_login'));

    // User is redirected.
    $this->drupalLogin($this->user);
    $this->assertSession()->addressEquals("user/{$this->user->id()}/moderation/dashboard");
  }

  /**
   * Tests disabled redirect on login.
   */
  public function testDisabled() {
    // Set redirect to disabled.
    $this->config('moderation_dashboard.settings')
      ->set('redirect_on_login', FALSE)
      ->save();

    // User is not redirected.
    $this->drupalLogin($this->user);
    $this->assertSession()->addressEquals("user/{$this->user->id()}");
  }

  /**
   * Tests if settings form is working as expected.
   */
  public function testSettingsForm() {
    $admin = $this->createUser([], NULL, TRUE);
    $assert_session = $this->assertSession();

    $this->drupalLogin($admin);
    $this->drupalGet('admin/config/people/moderation_dashboard');

    // Disabling redirect on login.
    $this->submitForm([
      'redirect_on_login' => FALSE,
    ], 'Save configuration');

    $status_message = $assert_session->elementExists('css', 'div[role="contentinfo"]')->getText();
    $this->assertSame('Status message The configuration options have been saved.', $status_message);
    $this->assertSame(FALSE, $this->config('moderation_dashboard.settings')->get('redirect_on_login'));

    // Enabling redirect on login.
    $this->submitForm([
      'redirect_on_login' => TRUE,
    ], 'Save configuration');

    $status_message = $assert_session->elementExists('css', 'div[role="contentinfo"]')->getText();
    $this->assertSame('Status message The configuration options have been saved.', $status_message);
    $this->assertSame(TRUE, $this->config('moderation_dashboard.settings')->get('redirect_on_login'));
  }

}
