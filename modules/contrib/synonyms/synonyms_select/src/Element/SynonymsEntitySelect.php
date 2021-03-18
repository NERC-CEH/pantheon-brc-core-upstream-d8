<?php

namespace Drupal\synonyms_select\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Select;

/**
 * Form element for synonyms-friendly entity select.
 *
 * @FormElement("synonyms_entity_select")
 */
class SynonymsEntitySelect extends Select {

  /**
   * Delimiter to use when separating entity ID and its synonym.
   *
   * @var string
   */
  const DELIMITER = ':';

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();

    array_unshift($info['#process'], [
      get_class($this), 'elementSynonymsEntitySelect',
    ]);
    $info['#element_validate'][] = [
      get_class($this), 'validateEntitySelect',
    ];
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $return = parent::valueCallback($element, $input, $form_state);

    if (is_null($return) && isset($element['#default_value'])) {
      $return = $element['#default_value'];
    }

    // Force default value (entity ID(-s)) to be strings. Otherwise we are
    // hitting the situation when all synonyms are highlighted as selected.
    // This code snippet explains the problem:
    // $a = [25];
    // $k = '25:25';
    // in_array($k, $a); // Yields TRUE, because PHP seems to compare
    // int to int and not string-wise.
    if (is_array($return)) {
      $return = array_map(function ($item) {
        return (string) $item;
      }, $return);
    }
    elseif (!is_null($return)) {
      $return = (string) $return;
    }

    return $return;
  }

  /**
   * Form element process callback for 'synonyms_entity_select' type.
   */
  public static function elementSynonymsEntitySelect(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $options = [];

    $selection = \Drupal::service('plugin.manager.entity_reference_selection')->getInstance([
      'target_type' => $element['#target_type'],
      'target_bundles' => $element['#target_bundles'],
      'entity' => NULL,
    ]);

    $bundle_info = \Drupal::getContainer()->get('entity_type.bundle.info')->getBundleInfo($element['#target_type']);

    $referenceable_entities = $selection->getReferenceableEntities();
    $entities = [];

    foreach ($referenceable_entities as $bundle_entity_ids) {
      $entities = array_merge($entities, array_keys($bundle_entity_ids));
    }

    if (!empty($entities)) {
      $entities = \Drupal::entityTypeManager()->getStorage($element['#target_type'])->loadMultiple($entities);
    }

    foreach ($referenceable_entities as $bundle => $entity_ids) {
      $synonyms = \Drupal::getContainer()->get('synonyms.behavior.select')->getSynonymsMultiple(array_intersect_key($entities, $entity_ids));

      foreach ($entity_ids as $entity_id => $label) {
        $options[$entity_id] = $label;

        foreach ($synonyms[$entity_id] as $synonym) {
          $options[$entity_id . self::DELIMITER . $synonym['synonym']] = $synonym['wording'];
        }
      }
    }

    $element['#options'] = $options;

    return $element;
  }

  /**
   * Form element validation handler for synonyms_entity_select elements.
   */
  public static function validateEntitySelect(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $value = $form_state->getValue($element['#parents']);
    if (!isset($element['#multiple']) || !$element['#multiple']) {
      $value = [$value];
    }

    $unique = [];
    foreach ($value as $v) {
      if (!empty($v) || $v == 0) {
        if (!is_numeric($v)) {
          $v = explode(self::DELIMITER, $v, 2)[0];
        }
        $unique[$v] = $v;
      }
    }

    $items = [];
    foreach ($unique as $v) {
      $items[] = [
        $element['#key_column'] => $v,
      ];
    }

    if (!isset($element['#multiple']) || !$element['#multiple']) {
      $items = reset($items);
    }

    $form_state->setValueForElement($element, $items);
  }

}
