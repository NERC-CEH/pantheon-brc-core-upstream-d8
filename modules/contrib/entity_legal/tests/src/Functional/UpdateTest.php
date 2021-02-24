<?php

namespace Drupal\Tests\entity_legal\Functional;

use Drupal\entity_legal\Entity\EntityLegalDocument;
use Drupal\entity_legal\Entity\EntityLegalDocumentVersion;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update scripts.
 *
 * @group entity_legal
 */
class UpdateTest extends UpdatePathTestBase {

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
      // Apply a database patch that adds legal document versions:
      // - Versions v1, v2, v3 (published) in legal_notice.
      // - Versions v7, v8 (published) in privacy_policy.
      __DIR__ . '/../../fixtures/update/update_8200.php',
    ];
  }

  /**
   * Tests entity_legal_update_8200().
   *
   * @see entity_legal_update_8200()
   */
  public function testUpdate8200() {
    $factory = \Drupal::configFactory();
    $published_versions = [];
    foreach ($factory->listAll('entity_legal.document.') as $name) {
      list(, , $id) = explode('.', $name);
      $published_version = $factory->get($name)->get('published_version');

      // Check that the 'published_version' property exists.
      $this->assertNotNull($published_version);
      // Check that the 'published_version' property has the proper pattern.
      $this->assertStringStartsWith($id . '_', $published_version);
      // Save the values for later checks.
      $published_versions[$id] = $published_version;
    }

    // Check that the 'published' field doesn't exist yet.
    $field_exist = \Drupal::database()
      ->schema()
      ->fieldExists('entity_legal_document_version_data', 'published');
    $this->assertFalse($field_exist);

    // Run updates.
    $this->runUpdates();

    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    foreach (EntityLegalDocument::loadMultiple() as $document) {
      // Check that the 'published_version' has been removed.
      $this->assertArrayNotHasKey('published_version', $document->toArray());

      // Check that the published version value was been correctly transferred.
      $published_version_entity = $document->getPublishedVersion();
      $this->assertSame($published_versions[$document->id()], $published_version_entity->id());

      $ids = \Drupal::entityQuery(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
        ->condition('document_name', $document->id())
        ->execute();

      foreach (EntityLegalDocumentVersion::loadMultiple($ids) as $id => $document_version) {
        // Check the new 'published' field on each legal document version.
        $this->assertEquals(in_array($id, $published_versions), $document_version->isPublished());
      }
    }
  }

}
