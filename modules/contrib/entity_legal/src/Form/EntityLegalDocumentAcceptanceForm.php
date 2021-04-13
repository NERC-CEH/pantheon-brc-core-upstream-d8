<?php

namespace Drupal\entity_legal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\entity_legal\EntityLegalDocumentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form for deleting a custom block entity.
 */
class EntityLegalDocumentAcceptanceForm extends FormBase {

  /**
   * The Entity Legal Document used by this form.
   *
   * @var \Drupal\entity_legal\EntityLegalDocumentInterface
   */
  protected $document;

  /**
   * The private temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * Builds a new form instance.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store_factory
   *   The private temp store factory service.
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store_factory) {
    $this->tempStore = $private_temp_store_factory->get('entity_legal');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('tempstore.private'));
  }

  /**
   * Sets the legal document.
   *
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $document
   *   The legal document.
   */
  public function setDocument(EntityLegalDocumentInterface $document) {
    $this->document = $document;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_legal_document_acceptance_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $has_agreed = $this->document->userHasAgreed();

    $form['agree'] = [
      '#title' => $this->document->getAcceptanceLabel(),
      '#type' => 'checkbox',
      '#required' => TRUE,
      '#default_value' => $has_agreed,
      '#disabled' => $has_agreed,
    ];

    $form['submit'] = [
      '#value' => t('Submit'),
      '#type' => 'submit',
      '#access' => !$has_agreed,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $published_version = $this->document->getPublishedVersion();
    \Drupal::entityTypeManager()
      ->getStorage(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME)
      ->create([
        'document_version_name' => $published_version->id(),
      ])
      ->save();

    // Restore potential postponed messages and show them on the correct page.
    // @see \Drupal\entity_legal\Plugin\EntityLegal\Redirect::execute()
    if ($grouped_messages = $this->tempStore->get('postponed_messages')) {
      foreach ($grouped_messages as $type => $messages) {
        foreach ($messages as $message) {
          $this->messenger()->addMessage($message, $type);
        }
      }
      $this->tempStore->delete('postponed_messages');
    }
  }

}
