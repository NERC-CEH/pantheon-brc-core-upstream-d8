<?php

namespace Drupal\synonyms_autocomplete\SynonymsService\Behavior;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\ProviderInterface\FindInterface;
use Drupal\synonyms\BehaviorInterface\BehaviorInterface;
use Drupal\synonyms\BehaviorInterface\WidgetInterface;
use Drupal\synonyms\SynonymsService\ProviderService;

/**
 * Synonyms behavior service for autocomplete.
 */
class AutocompleteService implements BehaviorInterface, WidgetInterface {

  use StringTranslationTrait;

  /**
   * The key value.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * The entity reference selection handler plugin manager.
   *
   * @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface
   */
  protected $selectionManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The synonyms provider service.
   *
   * @var \Drupal\synonyms\SynonymsService\ProviderService
   */
  protected $providerService;

  /**
   * AutocompleteService constructor.
   */
  public function __construct(KeyValueFactoryInterface $key_value, SelectionPluginManagerInterface $selection_plugin_manager, Connection $database, EntityTypeManagerInterface $entity_type_manager, ProviderService $provider_service) {
    $this->keyValue = $key_value->get('synonyms_entity_autocomplete');
    $this->selectionManager = $selection_plugin_manager;
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->providerService = $provider_service;
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return 'autocomplete';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Autocomplete');
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetTitle() {
    return $this->t('Synonyms-friendly autocomplete');
  }

  /**
   * Execute synonym-friendly lookup of entities by a given keyword.
   *
   * @param string $keyword
   *   Keyword to search for.
   * @param string $key_value_key
   *   Key under which additional settings about the lookup are stored in
   *   key-value storage.
   *
   * @return array
   *   Array of looked up suggestions. Each array will have the following
   *   structure:
   *   - entity_id: (int) ID of the entity which this entry represents
   *   - entity_label: (string) Label of the entity which this entry represents
   *   - wording: (string) Wording with which this entry should be shown to the
   *     end user on the UI
   */
  public function autocompleteLookup($keyword, $key_value_key) {
    $suggestions = [];

    if ($this->keyValue->has($key_value_key)) {
      $settings = $this->keyValue->get($key_value_key);

      $suggested_entity_ids = [];

      $target_bundles = $settings['target_bundles'];
      $options = [
        'target_type' => $settings['target_type'],
        'handler' => 'default',
      ];
      if (!empty($target_bundles)) {
        $options['target_bundles'] = $target_bundles;
      }
      elseif (!$this->entityTypeManager->getDefinition($settings['target_type'])->hasKey('bundle')) {
        $target_bundles = [$settings['target_type']];
      }

      $handler = $this->selectionManager->getInstance($options);

      foreach ($handler->getReferenceableEntities($keyword, $settings['match'], $settings['suggestion_size']) as $suggested_entities) {
        foreach ($suggested_entities as $entity_id => $entity_label) {
          $suggestions[] = [
            'entity_id' => $entity_id,
            'entity_label' => $entity_label,
            'wording' => $entity_label,
          ];
          if ($settings['suggest_only_unique']) {
            $suggested_entity_ids[] = $entity_id;
          }
        }
      }

      if (count($suggestions) < $settings['suggestion_size']) {
        foreach ($target_bundles as $target_bundle) {
          if ($this->providerService->serviceIsEnabled($settings['target_type'], $target_bundle, $this->getId())) {
            foreach ($this->providerService->getSynonymConfigEntities($settings['target_type'], $target_bundle) as $synonym_config) {
              $plugin_instance = $synonym_config->getProviderPluginInstance();

              $condition = new Condition('AND');
              switch ($settings['match']) {
                case 'CONTAINS':
                  $condition->condition(FindInterface::COLUMN_SYNONYM_PLACEHOLDER, '%' . $this->database->escapeLike($keyword) . '%', 'LIKE');
                  break;

                case 'STARTS_WITH':
                  $condition->condition(FindInterface::COLUMN_SYNONYM_PLACEHOLDER, $this->database->escapeLike($keyword) . '%', 'LIKE');
                  break;
              }

              if (!empty($suggested_entity_ids)) {
                $condition->condition(FindInterface::COLUMN_ENTITY_ID_PLACEHOLDER, $suggested_entity_ids, 'NOT IN');
              }

              foreach ($plugin_instance->synonymsFind($condition) as $row) {
                if (!in_array($row->entity_id, $suggested_entity_ids)) {
                  $suggestions[] = [
                    'entity_id' => $row->entity_id,
                    'entity_label' => NULL,
                    'synonym' => $row->synonym,
                    'synonym_config_entity' => $synonym_config,
                    'wording' => NULL,
                  ];
                }

                if ($settings['suggest_only_unique']) {
                  $suggested_entity_ids[] = $row->entity_id;
                }

                if (count($suggestions) == $settings['suggestion_size']) {
                  break(2);
                }
              }
            }
          }
        }
      }

      $ids = [];
      foreach ($suggestions as $suggestion) {
        if (!$suggestion['entity_label']) {
          $ids[] = $suggestion['entity_id'];
        }
      }
      $ids = array_unique($ids);

      if (!empty($ids)) {
        $entities = $this->entityTypeManager->getStorage($settings['target_type'])
          ->loadMultiple($ids);

        foreach ($suggestions as $k => $suggestion) {
          if (!$suggestion['entity_label']) {
            $suggestions[$k]['entity_label'] = $entities[$suggestion['entity_id']]->label();
            $suggestions[$k]['wording'] = $suggestion['synonym_config_entity']->getProviderPluginInstance()->synonymFormatWording($suggestion['synonym'], $entities[$suggestion['entity_id']], $suggestion['synonym_config_entity'], $this->getId());
          }
        }
      }
    }

    return $suggestions;
  }

}
