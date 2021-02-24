<?php

namespace Drupal\entity_legal;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a entity legal document acceptance entity.
 */
interface EntityLegalDocumentAcceptanceInterface extends ContentEntityInterface {

  /**
   * Get the document version this acceptance belongs to.
   *
   * @return \Drupal\entity_legal\EntityLegalDocumentVersionInterface
   *   The version of the document corresponding to this acceptance.
   */
  public function getDocumentVersion();

}
