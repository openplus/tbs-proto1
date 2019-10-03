<?php

namespace Drupal\Tests\locale\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that entity type definitions can be serialized when Locale is present.
 *
 * @group locale
 */
class EntityTypeSerializationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_test',
    'field',
    'file',
    'language',
    'locale',
    'text',
  ];

  public function testEntityTypeSerializationWithTranslationService() {
    /** @var \Drupal\Core\Entity\ContentEntityType $entity_type */
    $entity_type = $this->container
      ->get('entity_type.manager')
      ->getDefinition('entity_test');

    // Because the entity_test entity type does not explicitly define its plural
    // label, this will result in a call to getStringTranslation(), which will
    // implicitly cause $entity_type to reference the string_translation service
    // and therefore become impossible to serialize due to the implicit
    // dependency on the database connection (via locale's translator).
    $entity_type->getPluralLabel();
    serialize($entity_type);
  }

}
