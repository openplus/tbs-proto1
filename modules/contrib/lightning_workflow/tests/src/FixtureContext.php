<?php

namespace Drupal\Tests\lightning_workflow;

use Drupal\block\Entity\Block;
use Drupal\lightning_core\ConfigHelper as Config;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\lightning_core\FixtureBase;
use Drupal\user\Entity\Role;
use Drupal\views\Entity\View;

final class FixtureContext extends FixtureBase {

  /**
   * @BeforeScenario
   */
  public function setUp() {
    // Create the administrator role if it does not already exist.
    if (! Role::load('administrator')) {
      $role = Role::create([
        'id' => 'administrator',
        'label' => 'Administrator'
      ])->setIsAdmin(TRUE);

      $this->save($role);
    }

    if (! $this->container->get('module_handler')->moduleExists('lightning_page')) {
      $config = new Config(
        $this->container->get('extension.list.module')->get('lightning_page'),
        $this->container->get('config.factory'),
        $this->container->get('entity_type.manager')
      );
      $config->deleteAll();
    }

    // Install Lightning Page separately in order to ensure that the optional
    // Pathauto config that it ships is installed too.
    $this->installModule('lightning_page');
    // Lightning Workflow optionally integrates with Diff, and for testing
    // purposes we'd like to enable that integration. In order to test with
    // meaningful responsibility-based roles, we also enable Lightning Roles.
    $this->installModule('lightning_roles');
    $this->installModule('pathauto');
    $this->installModule('views');

    // Cache the original state of the editorial workflow.
    $this->config('workflows.workflow.editorial');

    // Add moderation to the page content type.
    /** @var \Drupal\node\NodeTypeInterface $node_type */
    $node_type = NodeType::load('page')
      ->setThirdPartySetting('lightning_workflow', 'workflow', 'editorial');
    $dependencies = $node_type->getDependencies();
    $dependencies['enforced']['module'][] = 'lightning_page';
    $node_type->set('dependencies', $dependencies)->save();
    lightning_workflow_node_type_insert($node_type);

    // Cache the original state of the content view.
    $this->config('views.view.content');

    // Allow the content view to filter by moderation state.
    $view = View::load('content')->enforceIsNew();
    lightning_workflow_view_presave($view);
    $view->enforceIsNew(FALSE)->save();

    // Ensure that the main content block exists.
    $values = [
      'theme' => $this->container->get('theme.manager')->getActiveTheme()->getName(),
      'plugin' => 'system_main_block',
    ];

    $main_content_block = $this->container->get('entity_type.manager')
      ->getStorage('block')
      ->loadByProperties($values);

    if (empty($main_content_block)) {
      $values['id'] = $values['theme'] . '_content';
      $values['region'] = 'content';
      $block = Block::create($values);
      $this->save($block);
    }

    $this->installTheme('seven');
    $this->config('system.date')->clear('timezone.default')->save();
    $this->config('system.theme')->set('admin', 'seven')->save();
    $this->config('lightning_scheduler.settings')->set('time_step', 1)->save();
  }

  /**
   * @AfterScenario
   */
  public function tearDown() {
    parent::tearDown();
  }

}
