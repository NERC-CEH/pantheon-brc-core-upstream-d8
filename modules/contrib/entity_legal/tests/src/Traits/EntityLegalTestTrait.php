<?php

namespace Drupal\Tests\entity_legal\Traits;

use Drupal\entity_legal\EntityLegalDocumentInterface;

/**
 * Code reusing for Entity Legal tests.
 */
trait EntityLegalTestTrait {

  /**
   * Creates a random legal document entity.
   *
   * @param bool $require_signup
   *   Whether or not to require new users to agree.
   * @param bool $require_existing
   *   Whether or not to require existing users to agree.
   * @param array $settings
   *   Additional settings to pass through to the document.
   *
   * @return \Drupal\entity_legal\EntityLegalDocumentInterface
   *   The created legal document.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createDocument($require_signup = FALSE, $require_existing = FALSE, array $settings = []) {
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $entity */
    $entity = \Drupal::entityTypeManager()
      ->getStorage('entity_legal_document')
      ->create([
        'id' => $this->randomMachineName(32),
        'label' => $this->randomMachineName(),
        'require_signup' => (int) $require_signup,
        'require_existing' => (int) $require_existing,
        'settings' => $settings,
      ]);
    $entity->save();

    // Reset permissions cache to make new document permissions available.
    $this->checkPermissions([
      $entity->getPermissionView(),
      $entity->getPermissionExistingUser(),
    ]);

    return $entity;
  }

  /**
   * Creates a document version.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $document
   *   The document to add the version to.
   * @param bool $save_as_default
   *   Whether to save the version as the default for the document.
   *
   * @return \Drupal\entity_legal\EntityLegalDocumentVersionInterface
   *   The created legal document version.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createDocumentVersion(EntityLegalDocumentInterface $document, $save_as_default = FALSE) {
    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $entity */
    $entity = \Drupal::entityTypeManager()
      ->getStorage('entity_legal_document_version')
      ->create([
        'label' => $this->randomMachineName(),
        'name' => $this->randomMachineName(64),
        'document_name' => $document->id(),
        'acceptance_label' => 'I agree to the <a href="[entity_legal_document:url]">document</a>',
        'entity_legal_document_text' => [['value' => $this->randomMachineName()]],
      ]);
    $entity->save();

    if ($save_as_default) {
      $document->setPublishedVersion($entity);
      $document->save();
    }

    return $entity;
  }

  /**
   * Creates an account that is able to view and re-accept a given document.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $document
   *   The legal document the user is able to view and accept.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The user.
   */
  protected function createUserWithAcceptancePermissions(EntityLegalDocumentInterface $document) {
    $account = $this->drupalCreateUser([
      $document->getPermissionView(),
      $document->getPermissionExistingUser(),
    ]);

    return $account;
  }

}
