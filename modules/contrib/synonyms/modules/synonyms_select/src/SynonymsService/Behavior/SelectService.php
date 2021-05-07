<?php

namespace Drupal\synonyms_select\SynonymsService\Behavior;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\BehaviorInterface\BehaviorInterface;
use Drupal\synonyms\BehaviorInterface\WidgetInterface;
use Drupal\synonyms\SynonymsService\ProviderService;

/**
 * Synonyms behavior service for select widget.
 */
class SelectService implements BehaviorInterface, WidgetInterface {

  use StringTranslationTrait;

  /**
   * The synonyms provider service.
   *
   * @var \Drupal\synonyms\SynonymsService\ProviderService
   */
  protected $providerService;

  /**
   * SelectService constructor.
   */
  public function __construct(ProviderService $provider_service) {
    $this->providerService = $provider_service;
  }


  /**
   * {@inheritdoc}
   */
  public function getId() {
    return 'select';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Select');
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetTitle() {
    return $this->t('Synonyms-friendly select');
  }

  /**
   * Extract a list of synonyms from multiple entities.
   *
   * @param array $entities
   *   Array of entities from which to extract the synonyms. It should be keyed
   *   by entity ID and may only contain entities of the same type and bundle.
   *
   * @return array
   *   Array of synonyms. The returned array will be keyed by entity ID and the
   *   inner array will have the following structure:
   *   - synonym: (string) Synonym itself
   *   - wording: (string) Formatted wording with which this synonym should be
   *     presented to the end user
   */
  public function selectGetSynonymsMultiple(array $entities) {
    if (empty($entities)) {
      return [];
    }

    $synonyms = [];
    foreach ($entities as $entity) {
      $synonyms[$entity->id()] = [];
    }

    $entity_type = reset($entities)->getEntityTypeId();
    $bundle = reset($entities)->bundle();

    if ($this->providerService->serviceIsEnabled($entity_type, $bundle, $this->getId())) {
      foreach ($this->providerService->getSynonymConfigEntities($entity_type, $bundle) as $synonym_config) {
        foreach ($synonym_config->getProviderPluginInstance()->getSynonymsMultiple($entities) as $entity_id => $entity_synonyms) {
          foreach ($entity_synonyms as $entity_synonym) {
            $synonyms[$entity_id][] = [
              'synonym' => $entity_synonym,
              'wording' => $synonym_config->getProviderPluginInstance()->synonymFormatWording($entity_synonym, $entities[$entity_id], $synonym_config, $this->getId()),
            ];
          }
        }
      }
    }

    return $synonyms;
  }

}
