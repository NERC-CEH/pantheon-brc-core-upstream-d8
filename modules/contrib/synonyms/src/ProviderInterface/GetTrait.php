<?php

namespace Drupal\synonyms\ProviderInterface;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Trait to extract synonyms from an entity.
 */
trait GetTrait {

  /**
   * Fetch synonyms from multiple entities at once.
   *
   * @param array $entities
   *   Array of entities whose synonyms should be fetched. They array will be
   *   keyed by entity ID and all provided entities will be of the same entity
   *   type and bundle.
   *
   * @return array
   *   Array of extracted synonyms. It must be keyed by entity ID and each sub
   *   array should represent a list of synonyms that were extracted from the
   *   corresponding entity
   */
  public function getSynonymsMultiple(array $entities) {
    $synonyms = [];

    foreach ($entities as $entity_id => $entity) {
      $synonyms[$entity_id] = $this->getSynonyms($entity);
    }

    return $synonyms;
  }

  /**
   * Fetch synonyms from an entity.
   *
   * @param Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity whose synonyms should be fetched.
   *
   * @return string[]
   *   Array of extracted synonyms
   */
  abstract public function getSynonyms(ContentEntityInterface $entity);

}
