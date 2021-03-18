<?php

namespace Drupal\synonyms\SynonymsService;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Service that allows to look up entities by their synonyms.
 */
class FindSynonyms {

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * FindSynonyms constructor.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * Lookup entity IDs by the $condition.
   *
   * @param \Drupal\Core\Database\Query\Condition $condition
   *   Condition which defines what to search for.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   Entity type within which to search.
   * @param string|array $bundle
   *   Either single bundle string or array of such within which to search. NULL
   *   stands for no filtering by bundle, i.e. searching among all bundles.
   *
   * @return array
   *   Array of looked up synonyms/entities. Each element in this array will be
   *   an object with the following structure:
   *   - synonym: (string) synonym that was looked up
   *   - entity_id: (int) ID of the entity which this synonym belongs to
   */
  public function findSynonyms(Condition $condition, EntityTypeInterface $entity_type, $bundle = NULL) {
    if (!$entity_type->getKey('bundle')) {
      $bundle = $entity_type->id();
    }

    $lookup = [];

    if (is_null($bundle)) {
      $bundle = array_keys($this->entityTypeBundleInfo->getBundleInfo($entity_type->id()));
    }

    foreach (\Drupal::getContainer()->get('synonyms.provider_service')->getSynonymConfigEntities($entity_type->id(), $bundle) as $synonym_config) {
      foreach ($synonym_config->getProviderPluginInstance()->synonymsFind(clone $condition) as $synonym) {
        $lookup[] = $synonym;
      }
    }

    return $lookup;
  }

}
