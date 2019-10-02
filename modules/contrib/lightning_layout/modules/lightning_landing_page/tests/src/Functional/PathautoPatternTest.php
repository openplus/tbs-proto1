<?php

namespace Drupal\Tests\lightning_landing_page\Functional;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * @group lightning_layout
 * @group lightning_landing_page
 */
class PathautoPatternTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_landing_page',
    'pathauto',
  ];

  /**
   * Tests that Landing page nodes are available at path '/[node:title]'.
   */
  public function testLandingPagePattern() {
    $node = Node::create([
      'type' => 'landing_page',
      'title' => 'Foo Bar',
      'status' => NodeInterface::PUBLISHED,
      'uid' => 1,
    ]);
    $node->save();
    $this->drupalGet('/foo-bar');
    $this->assertSession()->pageTextContains('Foo Bar');
    $this->assertSession()->statusCodeEquals(200);
  }

}
