<?php

namespace Drupal\Tests\lightning_scheduler\Functional;

/**
 * @group lightning_workflow
 * @group lightning_scheduler
 */
class MigrationStatusPageTest extends MigrationTestBase {

  public function test() {
    parent::test();

    $assert = $this->assertSession();
    $text = 'Some content has not yet been migrated into the new base fields installed by Lightning Scheduler.';
    // parent::test() navigates to the migration UI, so we'll want to be sure
    // that the status page is linking to that.
    $url = parse_url($this->getSession()->getCurrentUrl(), PHP_URL_PATH);
    $this->assertNotEmpty($url);

    $this->drupalGet('/admin/reports/status');
    $assert->pageTextContains($text);
    $assert->linkByHrefExists($url);

    $this->container->get('lightning_scheduler.migrator')->setMigrations([]);
    $this->getSession()->reload();
    $assert->pageTextNotContains($text);
  }

}
