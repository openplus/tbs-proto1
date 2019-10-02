<?php

namespace Drupal\Tests\moderation_dashboard\Functional;

/**
 * Tests redirection to dashboard on login.
 *
 * @group moderation_dashboard
 */
class ModerationDashboardRedirectTest extends ModerationDashboardTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->config('system.site')
      ->set('page.403', '/user/login')
      ->save();
    $this->config('moderation_dashboard.settings')
      ->set('redirect_on_login', TRUE)
      ->save();
  }

  /**
   * Tests if login is redirected when the user didn't request another page.
   */
  public function testRedirect() {
    $this->drupalGet('user/login');
    $this->submitForm([
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->passRaw,
    ], t('Log in'));
    $this->assertSession()->addressEquals("user/{$this->user->id()}/moderation/dashboard");
  }

  /**
   * Tests if login is not redirected when the user requested another page.
   */
  public function testNoRedirect() {
    // Anonymous user is redirected to login screen.
    // After successful login the requested page is shown.
    $this->drupalGet('admin/content');
    $this->submitForm([
      'name' => $this->user->getDisplayName(),
      'pass' => $this->user->passRaw,
    ], t('Log in'));
    $this->assertSession()->addressEquals('admin/content');
  }

}
