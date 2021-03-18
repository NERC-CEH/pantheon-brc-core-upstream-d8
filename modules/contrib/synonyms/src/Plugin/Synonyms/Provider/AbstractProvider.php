<?php

namespace Drupal\synonyms\Plugin\Synonyms\Provider;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\ProviderInterface\ProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\synonyms\SynonymInterface;

/**
 * Good starting point for a synonyms provider plugin.
 */
abstract class AbstractProvider extends PluginBase implements ProviderInterface, ContainerFactoryPluginInterface, ProviderConfigurableInterface {

  use StringTranslationTrait;

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

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'wording' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, array $configuration, SynonymInterface $synonym_config) {
    $replacements = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => [],
    ];
    foreach ($synonym_config->getProviderPluginInstance()->formatWordingAvailableTokens() as $token => $token_info) {
      $replacements['#items'][] = Html::escape($token) . ': ' . $token_info;
    }

    $replacements = \Drupal::service('renderer')->renderRoot($replacements);
    $wording = isset($configuration['wording']) ? $configuration['wording'] : '';

    $form['wording'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wording for this provider'),
      '#default_value' => $wording,
      '#description' => $this->t('Specify the wording with which this entry should be presented. Available replacement tokens are: @replacements Note: To avoid unnecessary complexity there is no per-widget wording configuration here at provider level. So, this wording will be used by all installed synonyms-friendly widgets.', [
        '@replacements' => $replacements,
      ]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state, SynonymInterface $synonym_config) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, SynonymInterface $synonym_config) {
    return [
      'wording' => $form_state->getValue('wording'),
    ];
  }

}
