<?php

namespace Drupal\synonyms\SynonymsService;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\synonyms\ProviderInterface\FindInterface;

/**
 * A collection of handy provider-related methods.
 */
class ProviderService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * BehaviorService constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * Retrieve a list of entity synonyms.
   *
   * @param Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity for which to conduct the search.
   *
   * @return string[]
   *   The array of known synonyms for this entity
   */
  public function getEntitySynonyms(ContentEntityInterface $entity) {
    $synonyms = [];

    foreach ($this->getSynonymConfigEntities($entity->getEntityTypeId(), $entity->bundle()) as $synonym_config) {
      $synonyms = array_merge($synonyms, $synonym_config->getProviderPluginInstance()->getSynonyms($entity));
    }

    return array_unique($synonyms);
  }

  /**
   * Get a list of enabled synonym providers.
   *
   * @param string $entity_type
   *   Entity type for which to conduct the search.
   * @param string|array $bundle
   *   Single bundle or an array of them for which to conduct the search. If
   *   null is given, then no restrictions are applied on bundle level.
   *
   * @return \Drupal\synonyms\Entity\Synonym[]
   *   The array of enabled synonym providers
   */
  public function getSynonymConfigEntities($entity_type, $bundle) {
    $entities = [];

    if (is_scalar($bundle) && !is_null($bundle)) {
      $bundle = [$bundle];
    }

    foreach ($this->entityTypeManager->getStorage('synonym')->loadMultiple() as $synonym_config) {
      $provider_instance = $synonym_config->getProviderPluginInstance();
      $provider_definition = $provider_instance->getPluginDefinition();
      if ($provider_definition['controlled_entity_type'] == $entity_type && (!is_array($bundle) || in_array($provider_definition['controlled_bundle'], $bundle))) {
        $entities[] = $synonym_config;
      }
    }

    return $entities;
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

    foreach ($this->getSynonymConfigEntities($entity_type->id(), $bundle) as $synonym_config) {
      foreach ($synonym_config->getProviderPluginInstance()->synonymsFind(clone $condition) as $synonym) {
        $lookup[] = $synonym;
      }
    }

    return $lookup;
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
  public function getBySynonym(EntityTypeInterface $entity_type, $name, $bundle = NULL) {

    $found_entity_ids = [];

    if ($entity_type->id() == 'user' || $entity_type->hasKey('label')) {

      // User entity type does not declare its label, while it does have one.
      $label_column = $entity_type->id() == 'user' ? 'name' : $entity_type->getKey('label');

      $query = $this->entityTypeManager->getStorage($entity_type->id())->getQuery();
      $query->condition($label_column, $name);
      if ($entity_type->hasKey('bundle') && $bundle) {
        $query->condition($entity_type->getKey('bundle'), $bundle);
      }
      if ($result = $query->execute()) {
        foreach ($result as $id) {
          $found_entity_ids[] = $id;
        }
      }
    }

    $condition = new Condition('AND');
    $condition->condition(FindInterface::COLUMN_SYNONYM_PLACEHOLDER, $name);

    foreach ($this->findSynonyms($condition, $entity_type, $bundle) as $item) {
      $found_entity_ids[] = $item->entity_id;
    }

    return array_unique($found_entity_ids);
  }

  /**
   * Checks if the service is enabled.
   *
   * @param string $entity_type
   *   Entity type for which to do the check.
   * @param string $bundle
   *   Bundle for which to do the check.
   * @param string $service_id
   *   ID of the synonyms behavior service for check.
   *
   * @return bool
   *   Returns TRUE if this service is enabled for given
   *   entity type and bundle and FALSE if it is not.
   */
  public function serviceIsEnabled($entity_type, $bundle, $service_id) {
    $status = \Drupal::config('synonyms_' . $service_id . '.behavior.' . $entity_type . '.' . $bundle)->get('status');

    return !empty($status);
  }

}
