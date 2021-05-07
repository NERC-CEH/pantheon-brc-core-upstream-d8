<?php

namespace Drupal\synonyms\Entity;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\synonyms\SynonymInterface;
use Drupal\synonyms\ProviderPluginCollection;

/**
 * Synonym configuration entity.
 *
 * @ConfigEntityType(
 *   id = "synonym",
 *   label = @Translation("Synonym configuration"),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Config\Entity\ConfigEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\synonyms_ui\Form\SynonymForm",
 *       "edit" = "Drupal\synonyms_ui\Form\SynonymForm",
 *       "delete" = "Drupal\synonyms_ui\Form\SynonymDeleteForm"
 *     }
 *   },
 *   config_prefix = "synonym",
 *   admin_permission = "administer synonyms",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   config_export = {
 *     "id",
 *     "provider_plugin",
 *     "base_provider_plugin",
 *     "provider_configuration"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/synonyms/{synonym}",
 *     "delete-form" = "/admin/structure/synonyms/{synonym}/delete"
 *   }
 * )
 */
class Synonym extends ConfigEntityBase implements SynonymInterface {

  /**
   * Plugin ID that corresponds to this config entry.
   *
   * @var string
   */
  protected $provider_plugin;

  /**
   * Base plugin ID that corresponds to this config entry.
   *
   * @var string
   */
  protected $base_provider_plugin;

  /**
   * Plugin configuration.
   *
   * @var array
   */
  protected $provider_configuration = [];

  /**
   * The plugin collection that stores synonym provider plugins.
   *
   * @var \Drupal\synonyms\ProviderPluginCollection
   */
  protected $pluginCollection;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getProviderPluginInstance()->getPluginDefinition()['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderPluginInstance() {
    return $this->getPluginCollection()->get($this->getProviderPlugin());
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderPlugin() {
    return $this->provider_plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function setProviderPlugin($plugin) {
    $this->provider_plugin = $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderConfiguration() {
    $plugin = $this->getProviderPluginInstance();
    if ($plugin instanceof ConfigurableInterface) {
      return $plugin->getConfiguration();
    }
    return $this->provider_configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setProviderConfiguration(array $provider_configuration) {
    $plugin = $this->getProviderPluginInstance();
    if ($plugin instanceof ConfigurableInterface) {
      $plugin->setConfiguration($provider_configuration);
    }
    $this->provider_configuration = $provider_configuration;
  }

  /**
   * Gets the plugin collections used by this entity.
   *
   * @return \Drupal\synonyms\ProviderPluginCollection
   *   An array of plugin collections, keyed by the property name they use to
   *   store their configuration.
   */
  public function getPluginCollections() {
    return ['provider_configuration' => $this->getPluginCollection()];
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities) {
    parent::postLoad($storage, $entities);
    foreach ($entities as $entity) {
      $entity->addCacheTags([
        self::cacheTagConstruct(
          $entity->getProviderPluginInstance()->getPluginDefinition()['controlled_entity_type'],
          $entity->getProviderPluginInstance()->getPluginDefinition()['controlled_bundle']
        ),
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // Update the "static" properties. We keep them only to be able to leverage
    // Schema API through them.
    $this->base_provider_plugin = $this->getProviderPluginInstance()->getBaseId();

    // Make sure we have appropriate cache tags in this entity. If it was just
    // created and runs its first save it might not have it set up yet.
    $this->addCacheTags([
      self::cacheTagConstruct(
        $this->getProviderPluginInstance()->getPluginDefinition()['controlled_entity_type'],
        $this->getProviderPluginInstance()->getPluginDefinition()['controlled_bundle']
      ),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $return = parent::calculateDependencies();

    $plugin_definition = $this->getProviderPluginInstance()->getPluginDefinition();
    $controlled_entity_type = \Drupal::entityTypeManager()->getDefinition($plugin_definition['controlled_entity_type']);
    $dependency = $controlled_entity_type->getBundleConfigDependency($plugin_definition['controlled_bundle']);
    if ($dependency) {
      $this->addDependency($dependency['type'], $dependency['name']);
    }

    return $return;
  }

  /**
   * Construct a cache tag.
   *
   * Construct a cache tag that represents this synonyms config,
   * entity type, and bundle.
   *
   * @param string $entity_type
   *   Entity type whose cache tag is requested.
   * @param string $bundle
   *   Bundle whose cache tag is requested.
   *
   * @return string
   *   Cache tag
   */
  public static function cacheTagConstruct($entity_type, $bundle) {
    return 'synonyms:' . $entity_type . '.' . $bundle;
  }

  /**
   * Encapsulates the creation of entity's LazyPluginCollection.
   *
   * @return \Drupal\synonyms\ProviderPluginCollection
   *   The entity's plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection && $this->getProviderPlugin()) {
      $this->pluginCollection = new ProviderPluginCollection(\Drupal::service('plugin.manager.synonyms_provider'), $this->getProviderPlugin(), $this->provider_configuration);
    }
    return $this->pluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  protected function invalidateTagsOnSave($update) {
    parent::invalidateTagsOnSave($update);
    Cache::invalidateTags($this->cacheTags);
  }

  /**
   * {@inheritdoc}
   */
  protected static function invalidateTagsOnDelete(EntityTypeInterface $entity_type, array $entities) {
    $cacheability_metadata = new CacheableMetadata();
    foreach ($entities as $entity) {
      $cacheability_metadata->addCacheableDependency($entity);
    }
    Cache::invalidateTags($cacheability_metadata->getCacheTags());
  }

}
