<?php

namespace Drupal\synonyms_select\SynonymsService\Behavior;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\BehaviorInterface\BehaviorInterface;
use Drupal\synonyms\BehaviorInterface\WidgetInterface;

/**
 * Synonyms behavior service for select widget.
 */
class SelectService implements BehaviorInterface, WidgetInterface {

  use StringTranslationTrait;

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
   * Extract a list of synonyms from an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity from which to extract the synonyms.
   *
   * @return array
   *   Array of synonyms. Each sub array will have the following structure:
   *   - synonym: (string) Synonym itself
   *   - wording: (string) Formatted wording with which this synonym should be
   *     presented to the end user
   */
  public function getSynonyms(ContentEntityInterface $entity) {
    $synonyms = $this->getSynonymsMultiple([$entity->id() => $entity]);
    return $synonyms[$entity->id()];
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
  public function getSynonymsMultiple(array $entities) {
    if (empty($entities)) {
      return [];
    }

    $synonyms = [];
    foreach ($entities as $entity) {
      $synonyms[$entity->id()] = [];
    }

    $entity_type = reset($entities)->getEntityTypeId();
    $bundle = reset($entities)->bundle();

    if (\Drupal::getContainer()->get('synonyms.behavior_service')->serviceIsEnabled($entity_type, $bundle, $this->getId())) {
      foreach (\Drupal::getContainer()->get('synonyms.provider_service')->getSynonymConfigEntities($entity_type, $bundle) as $synonym_config) {
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
