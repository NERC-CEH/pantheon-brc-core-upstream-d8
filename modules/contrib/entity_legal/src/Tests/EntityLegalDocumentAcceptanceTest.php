<?php

namespace Drupal\entity_legal\Tests;

/**
 * Tests acceptance functionality for the legal document entity.
 *
 * @group entity_legal
 */
class EntityLegalDocumentAcceptanceTest extends EntityLegalTestBase {

  /**
   * Test that user has the ability to agree to legal documents.
   */
  public function testSubmissionForm() {
    $document = $this->createDocument(TRUE, TRUE);
    $version = $this->createDocumentVersion($document, TRUE);

    $acceptance_user = $this->drupalCreateUser([
      $document->getPermissionView(),
      $document->getPermissionExistingUser(),
    ]);

    $this->drupalLogin($acceptance_user);

    $document_url = $document->toUrl();

    $this->drupalGet($document_url);
    $this->assertFieldByName('agree', NULL, 'Agree checkbox found');
    $this->assertFieldByName('op', 'Submit', 'Submit button found');
    $this->assertText($document->label(), 'Document title found');
    $this->assertText($version->get('entity_legal_document_text')[0]->value, 'Version document text found');

    $this->drupalPostForm($document_url, [
      'agree' => 1,
    ], 'Submit');

    $this->drupalGet($document_url);
    // @todo Assert checkbox is disabled and acceptance date displayed.
    // $this->assertNoFieldByName('agree', NULL, 'Agree checkbox not found');
    $this->assertNoFieldByName('op', 'Submit', 'Submit button not found');

    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    $document = $this->getUncachedEntity(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME, $document->id());
    $this->assertTrue($document->userHasAgreed($acceptance_user), 'User has accepted');

    $new_version = $this->createDocumentVersion($document, TRUE);

    $document = $this->getUncachedEntity(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME, $document->id());
    $this->assertFalse($document->userHasAgreed($acceptance_user), 'User has not accepted new version');

    $this->drupalGet($document_url);
    $this->assertFieldByName('agree', NULL, 'Agree checkbox found');
    $this->assertFieldByName('op', 'Submit', 'Submit button found');
    $this->assertText($document->label(), 'Document title found');
    $this->assertText($new_version->get('entity_legal_document_text')[0]->value, 'New version document text found');

    $this->drupalPostForm($document_url, [
      'agree' => 1,
    ], 'Submit');

    $this->drupalGet($document_url);
    // @todo Assert checkbox is disabled and acceptance date displayed.
    // $this->assertNoFieldByName('agree', NULL, 'Agree checkbox not found');
    $this->assertNoFieldByName('op', 'Submit', 'Submit button not found');

    $document = $this->getUncachedEntity(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME, $document->id());
    $this->assertTrue($document->userHasAgreed($acceptance_user), 'User has accepted new version');

    $acceptance_storage = \Drupal::entityTypeManager()->getStorage(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME);

    // Check that removing a document version deletes all related acceptances.
    $version->delete();
    $aid_count = $acceptance_storage->getQuery()
      ->condition('uid', $acceptance_user->id())
      ->count()
      ->execute();
    $this->assertEqual(1, $aid_count);

    // Check that removing a user deletes all its acceptance records.
    $acceptance_user->delete();
    $aid_count = $acceptance_storage->getQuery()
      ->condition('uid', $acceptance_user->id())
      ->count()
      ->execute();
    $this->assertEqual(0, $aid_count);
  }

}
