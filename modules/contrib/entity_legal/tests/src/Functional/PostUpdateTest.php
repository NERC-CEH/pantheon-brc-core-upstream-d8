<?php

namespace Drupal\Tests\entity_legal\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the post-update functions.
 *
 * @group entity_legal
 */
class PostUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-8.6.0.bare.testing.php.gz',
      // Install the 'entity_legal' module.
      __DIR__ . '/../../fixtures/update/install_entity_legal.php',
      // Apply a database patch that contains two legal documents.
      __DIR__ . '/../../fixtures/update/legal_documents.php',
    ];
  }

  /**
   * Tests entity_legal_post_update_title_pattern().
   *
   * @see entity_legal_post_update_title_pattern()
   */
  public function testPostUpdateTitlePattern() {
    $factory = \Drupal::configFactory();
    foreach ($factory->listAll('entity_legal.document.') as $name) {
      $settings = $factory->get($name)->get('settings');
      // Check that the 'settings.title_pattern' property doesn't exist yet.
      $this->assertArrayNotHasKey('title_pattern', $settings);
    }

    // Run updates.
    $this->runUpdates();

    foreach ($factory->listAll('entity_legal.document.') as $name) {
      $settings = $factory->get($name)->get('settings');
      // Check that the 'settings.title_pattern' property has been added.
      $this->assertArrayHasKey('title_pattern', $settings);
      // Check the 'settings.title_pattern' property value.
      $this->assertSame('[entity_legal_document:published-version:label]', $settings['title_pattern']);
    }
  }

}
