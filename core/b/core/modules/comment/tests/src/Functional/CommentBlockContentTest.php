<?php

namespace Drupal\Tests\comment\Functional;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Url;
use Drupal\comment\Entity\CommentType;
use Drupal\comment\Tests\CommentTestTrait;
use Drupal\user\RoleInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests use of comment field on entity-type without a canonical path.
 *
 * @group comment
 */
class CommentBlockContentTest extends BrowserTestBase {

  use CommentTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['comment', 'user', 'block_content', 'block'];

  /**
   * An administrative user with permission to configure comment settings.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a bundle for entity_test.
    $type = BlockContentType::create([
      'id' => 'comments',
      'label' => 'Comments',
    ]);
    $type->save();
    block_content_add_body_field($type->id());
    CommentType::create([
      'id' => 'block_content_comment_type',
      'label' => 'Comment settings',
      'description' => 'Comment settings',
      'target_entity_type_id' => 'block_content',
    ])->save();
    // Create comment field on block_content bundle.
    $this->addDefaultCommentField('block_content', 'comments');

    // Create test user.
    $this->adminUser = $this->drupalCreateUser(array(
      'administer comments',
      'skip comment approval',
      'post comments',
      'access comments',
      'administer blocks',
    ));

    // Enable anonymous and authenticated user comments.
    user_role_grant_permissions(RoleInterface::ANONYMOUS_ID, array(
      'access comments',
      'post comments',
      'skip comment approval',
    ));

    // Create a block and place it.
    $this->drupalLogin($this->adminUser);
    $edit = array();
    $edit['info[0][value]'] = $this->randomMachineName(8);
    $body = $this->randomMachineName(16);
    $edit['body[0][value]'] = $body;
    $this->drupalPostForm('block/add', $edit, t('Save'));

    // Place the block.
    $instance = array(
      'id' => Unicode::strtolower($edit['info[0][value]']),
      'settings[label]' => $edit['info[0][value]'],
      'region' => 'sidebar_first',
    );
    $block = BlockContent::load(1);
    $url = 'admin/structure/block/add/block_content:' . $block->uuid() . '/' . $this->config('system.theme')->get('default');
    $this->drupalPostForm($url, $instance, t('Save block'));
    $this->drupalLogout();
  }

  /**
   * Tests anonymous commenting via a block.
   */
  public function testAnonymousBlockContentCommenting() {
    // Navigate to home page.
    $this->drupalGet('');
    // Comment on the block.
    $edit = [];
    $edit['comment_body[0][value]'] = 'Noni the pony is skinny and bony';
    $edit['subject[0][value]'] = 'Oh no, why does it go?';
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals(Url::fromRoute('user.login'));
  }

}
