<?php

namespace Drupal\Tests\entity_legal\Kernel;

use Drupal\entity_legal\Entity\EntityLegalDocument;
use Drupal\entity_legal\Entity\EntityLegalDocumentVersion;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the 'SingleLegalDocumentPublishedVersion' constraint validator.
 *
 * @group entity_legal
 */
class SingleLegalDocumentPublishedVersionConstraintTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_legal',
    'field',
    'text',
  ];

  /**
   * Tests the 'BundleUniqueFieldValue' constraint validator.
   */
  public function testBundleUniqueFieldValue() {
    $this->installConfig(['entity_legal']);
    $this->installEntitySchema('entity_legal_document_version');

    EntityLegalDocument::create([
      'id' => 'legal_notice',
      'label' => 'Legal notice',
    ])->save();

    $doc_v1 = EntityLegalDocumentVersion::create([
      'document_name' => 'legal_notice',
      'label' => 'v1.0',
      'acceptance_label' => 'Accept the legal notice',
    ]);

    // Check that validation passes.
    $violations = $doc_v1->validate();
    $this->assertCount(0, $violations);

    $doc_v1->save();

    // Reload the entity.
    $doc_v1 = EntityLegalDocumentVersion::load($doc_v1->id());

    // Check that the default value has been set to FALSE.
    $this->assertFalse($doc_v1->get('published')->value);

    $doc_v1->set('published', TRUE);

    // Check that validation passes.
    $violations = $doc_v1->validate();
    $this->assertCount(0, $violations);

    $doc_v1->save();

    // Reload the entity.
    $doc_v1 = EntityLegalDocumentVersion::load($doc_v1->id());

    // Check that the value has been changed to TRUE.
    $this->assertTrue($doc_v1->get('published')->value);

    // Create a 2nd version and try to set it as published as well.
    $doc_v2 = EntityLegalDocumentVersion::create([
      'document_name' => 'legal_notice',
      'label' => 'v2.0',
      'acceptance_label' => 'Accept the legal notice v2',
      'published' => TRUE,
    ]);

    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $doc_v2->validate();
    $this->assertCount(1, $violations);
    // Strip tags, so we can compare as plain text.
    $violation_message = strip_tags($violations[0]->getMessage());

    // Check that the proper violation message is received when trying to set
    // more than one version as published within a legal document.
    $this->assertEquals('A legal document can have only one published version. Legal notice v1.0 is already published and should be un-published before publishing this version.', $violation_message);

    $doc_v2->set('published', FALSE);
    // Check that more than one un-published versions are allowed .
    $violations = $doc_v2->validate();
    $this->assertCount(0, $violations);

    EntityLegalDocument::create([
      'id' => 'privacy_policy',
      'label' => 'Privacy policy',
    ])->save();

    $privacy_policy_v1 = EntityLegalDocumentVersion::create([
      'document_name' => 'privacy_policy',
      'label' => 'v1.0',
      'acceptance_label' => 'Accept the privacy policy',
    ]);

    // Check that another published version can live in other legal document.
    $violations = $privacy_policy_v1->validate();
    $this->assertCount(0, $violations);
  }

}
