<?php

namespace Drupal\entity_legal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Utility\Token;
use Drupal\entity_legal\EntityLegalDocumentInterface;
use Drupal\entity_legal\EntityLegalDocumentVersionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class EntityLegalController.
 *
 * @package Drupal\entity_legal\Controller
 */
class EntityLegalController extends ControllerBase {

  /**
   * The entity legal document version storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityLegalDocumentVersionStorage;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME),
      $container->get('token')
    );
  }

  /**
   * EntityLegalController constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_legal_document_version_storage
   *   The custom block storage.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(EntityStorageInterface $entity_legal_document_version_storage, Token $token) {
    $this->entityLegalDocumentVersionStorage = $entity_legal_document_version_storage;
    $this->token = $token;
  }

  /**
   * Page title callback for the Entity Legal Document edit form.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $entity_legal_document
   *   The Entity Legal Document entity.
   */
  public function documentEditPageTitle(EntityLegalDocumentInterface $entity_legal_document) {
    return $this->t('Edit %label', ['%label' => $entity_legal_document->label()]);
  }

  /**
   * Page callback for the Entity Legal Document.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $entity_legal_document
   *   The Entity Legal Document entity.
   * @param \Drupal\entity_legal\EntityLegalDocumentVersionInterface|null $entity_legal_document_version
   *   The Entity Legal Document version entity.
   */
  public function documentPage(EntityLegalDocumentInterface $entity_legal_document, EntityLegalDocumentVersionInterface $entity_legal_document_version = NULL) {
    if (is_null($entity_legal_document_version)) {
      $entity_legal_document_version = $entity_legal_document->getPublishedVersion();
      if (!$entity_legal_document_version) {
        throw new NotFoundHttpException();
      }
    }

    // If specified version is unpublished, display a message.
    if ($entity_legal_document_version->id() != $entity_legal_document->getPublishedVersion()->id()) {
      \Drupal::messenger()->addMessage('You are viewing an unpublished version of this legal document.', 'warning');
    }

    return \Drupal::entityTypeManager()
      ->getViewBuilder(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
      ->view($entity_legal_document_version);
  }

  /**
   * Page title callback for the Entity Legal Document.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $entity_legal_document
   *   The Entity Legal Document entity.
   * @param \Drupal\entity_legal\EntityLegalDocumentVersionInterface|null $entity_legal_document_version
   *   The Entity Legal Document version entity.
   */
  public function documentPageTitle(EntityLegalDocumentInterface $entity_legal_document, EntityLegalDocumentVersionInterface $entity_legal_document_version = NULL) {
    if (is_null($entity_legal_document_version)) {
      $entity_legal_document_version = $entity_legal_document->getPublishedVersion();
    }

    $pattern = $entity_legal_document->get('settings')['title_pattern'];

    return $this->token->replace($pattern, [
      ENTITY_LEGAL_DOCUMENT_ENTITY_NAME => $entity_legal_document,
    ]);
  }

  /**
   * Page callback for the Entity Legal Document Version form.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $entity_legal_document
   *   The entity legal document.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function documentVersionForm(EntityLegalDocumentInterface $entity_legal_document, Request $request) {
    $entity_legal_document_version = $this->entityLegalDocumentVersionStorage->create([
      'document_name' => $entity_legal_document->id(),
    ]);
    return $this->entityFormBuilder()->getForm($entity_legal_document_version);
  }

  /**
   * Page title callback for the Entity Legal Document Version add form.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $entity_legal_document
   *   The entity legal document.
   *
   * @return string
   *   The page title.
   */
  public function documentVersionAddFormTitle(EntityLegalDocumentInterface $entity_legal_document) {
    return $this->t('Add %type legal document version', ['%type' => $entity_legal_document->label()]);
  }

  /**
   * Page title callback for the Entity Legal Document Version edit form.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentVersionInterface $entity_legal_document_version
   *   The Entity Legal Document version entity.
   *
   * @return string
   *   The page title.
   */
  public function documentVersionEditFormTitle(EntityLegalDocumentVersionInterface $entity_legal_document_version) {
    return $this->t('Edit %label', ['%label' => $entity_legal_document_version->label()]);
  }

}
