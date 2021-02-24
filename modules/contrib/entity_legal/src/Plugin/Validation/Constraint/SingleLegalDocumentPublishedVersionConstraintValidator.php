<?php

namespace Drupal\entity_legal\Plugin\Validation\Constraint;

use Drupal\entity_legal\Entity\EntityLegalDocumentVersion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Provides a validator for the SingleLegalDocumentPublishedVersion constraint.
 */
class SingleLegalDocumentPublishedVersionConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    /** @var \Drupal\Core\Field\FieldItemListInterface $items */
    if ($items->isEmpty()) {
      return;
    }

    $published = $items->value;
    if (!$published) {
      // Don't validate anything if the FALSE has been set.
      return;
    }

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $items->getEntity();
    $entity_type_id = $entity->getEntityTypeId();

    if ($items->getFieldDefinition()->getName() !== 'published' || $entity_type_id !== 'entity_legal_document_version') {
      // The constraint has been set on wrong field.
      throw new \Exception("The SingleLegalDocumentPublishedVersion constraint cannot be set on other field than 'published' of 'entity_legal_document_version' entity type.");
    }

    $query = \Drupal::entityQuery($entity_type_id);
    // Using isset() instead of !empty() as 0 and '0' are valid ID values for
    // entity types using string IDs.
    if (isset($entity_id)) {
      $query->condition('id', $entity_id, '<>');
    }

    $ids = $query
      ->condition('published', TRUE)
      ->condition('document_name', $entity->bundle())
      ->range(0, 1)
      ->execute();

    if ($ids) {
      $id = reset($ids);
      $published_version = EntityLegalDocumentVersion::load($id);
      $legal_document = $published_version->get('document_name')->entity->label();
      $this->context->addViolation($constraint->message, [
        '%legal_document' => $legal_document,
        '%version' => $published_version->label(),
      ]);
    }
  }

}
