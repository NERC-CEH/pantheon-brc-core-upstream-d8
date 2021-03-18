<?php

namespace Drupal\synonyms\SynonymsService;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\synonyms\ProviderInterface\FindInterface;

/**
 * Service to look up an entity by its name or synonym.
 */
class EntityGetBySynonym {

  /**
   * The find synonyms service.
   *
   * @var \Drupal\synonyms\SynonymsService\FindSynonyms
   */
  protected $findSynonymsService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * EntityGetBySynonym constructor.
   */
  public function __construct(FindSynonyms $find_synonyms_service, EntityTypeManagerInterface $entity_type_manager) {
    $this->findSynonymsService = $find_synonyms_service;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Try finding entities by their name or synonym.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   What entity type is being searched.
   * @param string $name
   *   The look up keyword (the supposed name or synonym).
   * @param string $bundle
   *   Optionally limit the search within a specific bundle name of the provided
   *   entity type.
   *
   * @return array
   *   IDs of the looked up entities. If such entity is not found,
   *   an empty array is returned.
   */
  public function entityGetBySynonym(EntityTypeInterface $entity_type, $name, $bundle = NULL) {
    if ($entity_type->id() == 'user' || $entity_type->hasKey('label')) {

      // User entity type does not declare its label, while it does have one.
      $label_column = $entity_type->id() == 'user' ? 'name' : $entity_type->getKey('label');

      $query = $this->entityTypeManager->getStorage($entity_type->id())->getQuery();
      $query->condition($label_column, $name);
      if ($entity_type->hasKey('bundle') && $bundle) {
        $query->condition($entity_type->getKey('bundle'), $bundle);
      }

      $result = $query->execute();
      $result = reset($result);
      if ($result) {
        return $result;
      }
    }

    $condition = new Condition('AND');
    $condition->condition(FindInterface::COLUMN_SYNONYM_PLACEHOLDER, $name);

    $found_entity_ids = [];

    $synonyms_found = $this->findSynonymsService->findSynonyms($condition, $entity_type, $bundle, '*');
    if (isset($synonyms_found[0]->entity_id)) {
      $found_entity_ids[] = $synonyms_found[0]->entity_id;
    }

    return $found_entity_ids;
  }

}
