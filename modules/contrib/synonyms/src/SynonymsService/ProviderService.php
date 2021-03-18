<?php

namespace Drupal\synonyms\SynonymsService;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * BehaviorService constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
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

}
