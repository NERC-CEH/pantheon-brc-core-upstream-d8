<?php

namespace Drupal\synonyms\Plugin;

use Drupal\Core\Field\FieldItemList;

/**
 * Field item list of "synonyms" computed base field.
 */
class SynonymsFieldItemList extends FieldItemList {

  /**
   * {@inheritdoc}
   */
  public function getValue($include_computed = FALSE) {
    $synonyms = [];

    $entity = $this->getEntity();

    foreach (\Drupal::getContainer()->get('synonyms.provider_service')->getSynonymConfigEntities($entity->getEntityTypeId(), $entity->bundle()) as $synonym_config) {
      $synonyms = array_merge($synonyms, $synonym_config->getProviderPluginInstance()->getSynonyms($entity));
    }

    return array_unique($synonyms);
  }

}
