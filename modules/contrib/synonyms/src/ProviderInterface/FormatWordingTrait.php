<?php

namespace Drupal\synonyms\ProviderInterface;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\synonyms\SynonymInterface;

/**
 * Trait to format wording of a synonym.
 */
trait FormatWordingTrait {

  /**
   * Format a synonym into wording as requested by configuration.
   *
   * @param string $synonym
   *   Synonym that should be formatted.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to which this synonym belongs.
   * @param \Drupal\synonyms\SynonymInterface $synonym_config
   *   Synonym config entity in the context of which it all happens.
   * @param string $service_id
   *   The behavior service (widget) ID.
   *
   * @return string
   *   Formatted wording
   */
  public function synonymFormatWording($synonym, ContentEntityInterface $entity, SynonymInterface $synonym_config, $service_id) {
    // @todo Maybe we should use tokens replacement here? But then it would mean
    // an extra dependency on the tokens module. Is it worth it? For now let's
    // use stupid str_replace() and incorporate tokens only if user base really
    // asks for it.
    $wording_type = \Drupal::config('synonyms.settings')->get('wording_type');

    // If the wording type is 'No wording' it's simple.
    if ($wording_type == 'none') {
      return $synonym;
    }
    // If not... we have some job to do.
    else {
      $wording = '';
      $plugin_definition = $synonym_config->getProviderPluginInstance()->getPluginDefinition();
      $entity_type = $plugin_definition['controlled_entity_type'];
      $bundle = $plugin_definition['controlled_bundle'];
      // Try widget's wording per entity type and provider.
      if ($wording_type == 'provider') {
        $provider_configuration = $synonym_config->getProviderConfiguration();
        if (isset($provider_configuration['wording'])) {
          $get_wording = $provider_configuration['wording'];
        }
        $wording = !empty($get_wording) ? $get_wording : $wording;
      }
      // Try widget's wording per entity type.
      if (empty($wording) && ($wording_type == 'provider' || $wording_type == 'entity')) {
        $get_wording = \Drupal::config('synonyms.behavior.' . $entity_type . '.' . $bundle . '.' . $service_id)->get('wording');
        $wording = !empty($get_wording) ? $get_wording : $wording;
      }
      // Try default widget's wording and if it is empty as well
      // fallback to basic '@synonym' wording.
      if (empty($wording) && ($wording_type == 'provider' || $wording_type == 'entity' || $wording_type == 'default')) {
        $get_wording = \Drupal::config('synonyms_' . $service_id . '.widget')->get('default_wording');
        $wording = !empty($get_wording) ? $get_wording : $wording;
      }
      // Ultimate fallback if all other wordings are empty.
      if (empty($wording)) {
        $wording = '@synonym';
      }

      $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
      $field_label = $definitions[$plugin_definition['field']]->getLabel();

      $map = [
        '@synonym' => $synonym,
        '@entity_label' => $entity->label(),
        '@field_label' => strtolower($field_label),
        '@FIELD_LABEL' => strtoupper($field_label),
      ];

      return str_replace(array_keys($map), array_values($map), $wording);
    }
  }

  /**
   * Get available tokens for format wording.
   *
   * @return array
   *   Array of supported tokens in wording. Keys are the tokens whereas
   *   corresponding values are explanations about what each token means
   */
  public function formatWordingAvailableTokens() {
    return [
      '@synonym' => $this->t('The synonym value'),
      '@entity_label' => $this->t('The label of the entity this synonym belongs to'),
      '@field_label' => $this->t('The lowercase label of the provider field'),
      '@FIELD_LABEL' => $this->t('The uppercase label of the provider field'),
    ];
  }

}
