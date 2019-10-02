<?php

namespace Drupal\Tests\lightning_workflow\Kernel;

use Drupal\Core\Serialization\Yaml;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_workflow\Update\Update230;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\user\Entity\Role;
use Drupal\workflows\Entity\Workflow;
use Prophecy\Argument;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * @group lightning_workflow
 *
 * @coversDefaultClass \Drupal\lightning_workflow\Update\Update230
 */
class Update230Test extends KernelTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_moderation',
    'system',
    'user',
    'workflows',
  ];

  /**
   * The update runner under test.
   *
   * @var Update230
   */
  private $update;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->update = $this->container->get('class_resolver')
      ->getInstanceFromDefinition(Update230::class);
  }

  /**
   * @covers ::enableModerationSidebar
   */
  public function testEnableModerationSidebar() {
    $this->container->get('module_installer')->install(['lightning_roles']);
    $node_type = $this->createContentType()->id();

    $this->update->enableModerationSidebar();

    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $this->container->get('module_handler');
    $this->assertTrue($module_handler->moduleExists('moderation_sidebar'));
    $this->assertTrue($module_handler->moduleExists('toolbar'));

    $role = Role::load($node_type . '_creator');
    $this->assertInstanceOf(Role::class, $role);
  }

  /**
   * @covers ::alterTransitions
   */
  public function testAlterTransitions() {
    $workflow = <<<END
langcode: en
status: true
id: editorial
label: 'Editorial'
type: content_moderation
type_settings:
  states:
    archived:
      label: Archived
      weight: 5
      published: false
      default_revision: true
    draft:
      label: Draft
      published: false
      default_revision: false
      weight: -5
    published:
      label: Published
      published: true
      default_revision: true
      weight: 0
    review:
      label: 'In review'
      weight: -1
      published: false
      default_revision: false
  transitions:
    archive:
      label: Archive
      from:
        - published
      to: archived
      weight: 2
    archived_draft:
      label: 'Restore to Draft'
      from:
        - archived
      to: draft
      weight: 3
    archived_published:
      label: Restore
      from:
        - archived
      to: published
      weight: 4
    create_new_draft:
      label: 'Create New Draft'
      to: draft
      weight: 0
      from:
        - draft
        - published
    publish:
      label: Publish
      to: published
      weight: 1
      from:
        - draft
        - published
    review:
      label: Review
      to: review
      weight: 0
      from:
        - draft
        - review
  entity_types: {  }
END;
    $workflow = Yaml::decode($workflow);
    $this->assertSame(SAVED_NEW, Workflow::create($workflow)->save());

    $io = $this->prophesize(StyleInterface::class);
    $io->confirm( Argument::any() )->willReturn(TRUE);

    $this->update->alterTransitions($io->reveal());

    $workflow = Workflow::load($workflow['id']);
    $this->assertInstanceOf(Workflow::class, $workflow);
    /** @var Workflow $workflow */

    $plugin = $workflow->getTypePlugin();

    $this->assertSame('Send to review', $plugin->getTransition('review')->label());
    $this->assertSame('Restore from archive', $plugin->getTransition('archived_published')->label());

    $this->assertFalse($plugin->hasTransition('archived_draft'));
    $this->assertArrayHasKey('archived', $plugin->getTransition('create_new_draft')->from());
    $this->assertSame('create_new_draft', $plugin->getTransitionFromStateToState('archived', 'draft')->id());
  }

}
