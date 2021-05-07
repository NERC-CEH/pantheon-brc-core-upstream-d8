<?php

namespace Drupal\synonyms\ProviderInterface;

use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\synonyms\SynonymInterface;

/**
 * Provider configuration trait.
 */
trait ConfigurationTrait {

  use StringTranslationTrait;

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
