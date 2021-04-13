<?php

namespace Drupal\entity_legal\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Tests\entity_legal\Traits\EntityLegalTestTrait;

/**
 * Common Simpletest class for all legal tests.
 */
abstract class EntityLegalTestBase extends WebTestBase {

  use EntityLegalTestTrait;

  /**
   * The administrative user to use for tests.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block', 'entity_legal', 'field_ui', 'token'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'administer entity legal',
      'administer permissions',
      'administer user form display',
      'administer users',
    ]);

    // Ensure relevant blocks present if profile isn't 'standard'.
    if ($this->profile !== 'standard') {
      $this->drupalPlaceBlock('local_actions_block');
      $this->drupalPlaceBlock('page_title_block');
    }
  }

  /**
   * {@inheritdoc}
   *
   * Ensures generated names are lower case.
   */
  protected function randomMachineName($length = 8) {
    return strtolower(parent::randomMachineName($length));
  }

  /**
   * Get an entity bypassing static and db cache.
   *
   * @param string $entity_type
   *   The type of entity to get.
   * @param string $entity_id
   *   The ID or name to load the entity using.
   *
   * @return EntityLegalDocument
   *   The retrieved entity.
   */
  public function getUncachedEntity($entity_type, $entity_id) {
    $controller = \Drupal::entityTypeManager()->getStorage($entity_type);
    $controller->resetCache([$entity_id]);
    return $controller->load($entity_id);
  }

}
