<?php

namespace Drupal\synonyms\ProviderInterface;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface to extract (get) synonyms from an entity.
 */
interface GetInterface {

  /**
   * Fetch synonyms from an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity whose synonyms should be fetched.
   *
   * @return string[]
   *   Array of extracted synonyms
   */
  public function getSynonyms(ContentEntityInterface $entity);

  /**
   * Fetch synonyms from multiple entities at once.
   *
   * @param array $entities
   *   Array of entities whose synonyms should be fetched. The array will be
   *   keyed by entity ID and all provided entities will be of the same entity
   *   type and bundle.
   *
   * @return array
   *   Array of extracted synonyms. It must be keyed by entity ID and each sub
   *   array should represent a list of synonyms that were extracted from the
   *   corresponding entity
   */
  public function getSynonymsMultiple(array $entities);

}
