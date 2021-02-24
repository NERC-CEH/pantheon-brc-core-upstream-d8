<?php

namespace Drupal\entity_legal\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if there's only one published version in a legal document.
 *
 * @Constraint(
 *   id = "SingleLegalDocumentPublishedVersion",
 *   label = @Translation("Checks if there's only one published version in a legal document", context = "Validation"),
 * )
 */
class SingleLegalDocumentPublishedVersionConstraint extends Constraint {

  /**
   * Violation message.
   *
   * @var string
   */
  public $message = 'A legal document can have only one published version. %legal_document %version is already published and should be un-published before publishing this version.';

}
