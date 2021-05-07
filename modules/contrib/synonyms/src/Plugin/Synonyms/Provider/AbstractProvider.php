<?php

namespace Drupal\synonyms\Plugin\Synonyms\Provider;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\synonyms\ProviderInterface\ProviderInterface;
use Drupal\synonyms\ProviderInterface\ConfigurationInterface;
use Drupal\synonyms\ProviderInterface\ConfigurationTrait;
use Drupal\synonyms\ProviderInterface\FindInterface;
use Drupal\synonyms\ProviderInterface\FindTrait;
use Drupal\synonyms\ProviderInterface\FormatWordingInterface;
use Drupal\synonyms\ProviderInterface\FormatWordingTrait;
use Drupal\synonyms\ProviderInterface\GetInterface;
use Drupal\synonyms\ProviderInterface\GetTrait;

/**
 * Good starting point for a synonyms provider plugin.
 */
abstract class AbstractProvider extends PluginBase implements ProviderInterface, ContainerFactoryPluginInterface, ConfigurationInterface, GetInterface, FindInterface, FormatWordingInterface {

  use ConfigurationTrait, GetTrait, FindTrait, FormatWordingTrait;

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContainerInterface $container) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container
    );
  }

}
