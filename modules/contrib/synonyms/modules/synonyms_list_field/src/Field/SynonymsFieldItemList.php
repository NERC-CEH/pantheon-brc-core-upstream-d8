<?php

namespace Drupal\synonyms_list_field\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Field item list of "synonyms" computed base field.
 */
class SynonymsFieldItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $entity = $this->getEntity();

    $delta = 0;

    // This prepends the entity label to synonyms list if the
    // 'Include entity label' checkbox is checked in settings.
    if (\Drupal::config('synonyms_list_field.settings')->get('include_entity_label')) {
      $this->list[$delta] = $this->createItem($delta, $entity->label());
      $delta++;
    }

    foreach (\Drupal::service('synonyms.provider_service')->getEntitySynonyms($entity) as $synonym) {
      $this->list[$delta] = $this->createItem($delta, $synonym);
      $delta++;
    }
  }

}
