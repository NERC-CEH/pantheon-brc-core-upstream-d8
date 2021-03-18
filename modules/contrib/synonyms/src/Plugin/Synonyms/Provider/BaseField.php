<?php

namespace Drupal\synonyms\Plugin\Synonyms\Provider;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\ConditionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\synonyms\ProviderInterface\FindInterface;
use Drupal\synonyms\ProviderInterface\FindTrait;
use Drupal\synonyms\ProviderInterface\FormatWordingInterface;
use Drupal\synonyms\ProviderInterface\FormatWordingTrait;
use Drupal\synonyms\ProviderInterface\GetInterface;
use Drupal\synonyms\ProviderInterface\GetTrait;
use Drupal\synonyms\SynonymsService\FieldTypeToSynonyms;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide synonyms from base fields.
 *
 * @Provider(
 *   id = "base_field",
 *   deriver = "Drupal\synonyms\Plugin\Derivative\Field"
 * )
 */
class BaseField extends AbstractProvider implements GetInterface, FindInterface, FormatWordingInterface {

  use GetTrait, FindTrait, FormatWordingTrait;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The field type to synonyms.
   *
   * @var \Drupal\synonyms\SynonymsService\FieldTypeToSynonyms
   */
  protected $fieldTypeToSynonyms;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * BaseField constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entity_field_manager, FieldTypeToSynonyms $field_type_to_synonyms, EntityTypeManagerInterface $entity_type_manager, Connection $database, ContainerInterface $container) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $container);

    $this->entityFieldManager = $entity_field_manager;
    $this->fieldTypeToSynonyms = $field_type_to_synonyms;
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('synonyms.provider.field_type_to_synonyms'),
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSynonyms(ContentEntityInterface $entity) {
    $map = $this->fieldTypeToSynonyms->getSimpleFieldTypeToPropertyMap();
    $field_type = $entity->getFieldDefinition($this->getPluginDefinition()['field'])->getType();

    $synonyms = [];

    if (isset($map[$field_type])) {
      foreach ($entity->get($this->getPluginDefinition()['field']) as $item) {
        $synonyms[] = $item->{$map[$field_type]};
      }
    }

    return $synonyms;
  }

  /**
   * {@inheritdoc}
   */
  public function synonymsFind(ConditionInterface $condition) {
    $entity_type_definition = $this->entityTypeManager->getDefinition($this->getPluginDefinition()['controlled_entity_type']);
    $entity_keys = $entity_type_definition->getKeys();

    $query = $this->database->select($entity_type_definition->getDataTable(), 'base');
    $query->addField('base', $entity_keys['id'], 'entity_id');
    $query->addField('base', $this->getPluginDefinition()['field'], 'synonym');

    if ($this->getPluginDefinition()['controlled_entity_type'] != $this->getPluginDefinition()['controlled_bundle'] && isset($entity_keys['bundle']) && $entity_keys['bundle']) {
      $query->condition($entity_keys['bundle'], $this->getPluginDefinition()['controlled_bundle']);
    }

    $this->synonymsFindProcessCondition($condition, $this->getPluginDefinition()['field'], $entity_keys['id']);
    $query->condition($condition);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $field = $this->entityFieldManager->getFieldDefinitions($this->getPluginDefinition()['controlled_entity_type'], $this->getPluginDefinition()['controlled_bundle'])[$this->getPluginDefinition()['field']];
    return [
      $field->getConfigDependencyKey() => [$field->getConfigDependencyName()],
    ];
  }

}
