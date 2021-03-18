<?php

namespace Drupal\synonyms_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\ProviderInterface\FormatWordingTrait;
use Drupal\Component\Utility\Html;

/**
 * Defines a form that configures forms module settings.
 */
class SettingsForm extends ConfigFormBase {

  use StringTranslationTrait, FormatWordingTrait;

  /**
   * All available synonyms widgets.
   *
   * @var array
   */
  protected $widgets;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->widgets = \Drupal::getContainer()->get('synonyms.behavior_service')->getWidgetServices();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'synonyms_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    $config_names = ['synonyms.settings'];
    if ($this->widgets) {
      foreach ($this->widgets as $service_id => $service) {
        $config_names[] = 'synonyms_' . $service_id . '.widget';
      }
    }
    return $config_names;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['wording_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Wording type'),
      '#options' => $this->wordingTypeOptions(),
      '#default_value' => $this->config('synonyms.settings')->get('wording_type'),
      '#description' => $this->t('<strong>No wording:</strong> All synonyms suggestions inside all synonyms friendly widgets will be presented to the user with synonym labels only.<br><strong>Default wording:</strong> Provides one default (and customisable) wording per widget. Good enough for sites with simple synonyms usage.<br><strong>Per entity type:</strong> Enables per entity type specific wording for each widget at "Manage behaviors" form.<br><strong>Per entity type and field:</strong> Enables per field (provider) specific wording at "Manage providers" form. One wording is used by all widgets here.<br><br>'),
    ];

    // Bring in the wording format from FormatWordingTrait.
    $replacements = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => [],
    ];
    foreach ($this->formatWordingAvailableTokens() as $token => $token_info) {
      $replacements['#items'][] = Html::escape($token) . ': ' . $token_info;
    }
    $replacements = \Drupal::service('renderer')->renderRoot($replacements);

    // Default wordings.
    if ($this->widgets) {
      foreach ($this->widgets as $service_id => $service) {
        $form[$service_id] = [
          '#type' => 'textfield',
          '#title' => $this->t('@widget widget default wording', [
            '@widget' => $service->getWidgetTitle(),
          ]),
          '#default_value' => $this->config('synonyms_' . $service_id . '.widget')->get('default_wording'),
          '#description' => $this->t('Specify the wording with which @widget widget suggestions should be presented. Available replacement tokens are: @replacements This will also serve as a fallback wording if more specific wordings are left empty.<br><br>', [
            '@widget' => $service->getWidgetTitle(),
            '@replacements' => $replacements,
          ]),
          '#states' => [
            'invisible' => [
              ':input[name="wording_type"]' => ['value' => 'none'],
            ],
          ],
        ];
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('synonyms.settings')
      ->set('wording_type', $form_state->getValue('wording_type'))
      ->set('wording_type_label', $this->wordingTypeOptions()[$form_state->getValue('wording_type')])
      ->save();
    parent::submitForm($form, $form_state);

    if ($this->widgets) {
      foreach ($this->widgets as $service_id => $service) {
        $this->config('synonyms_' . $service_id . '.widget')
          ->set('default_wording', $form_state->getValue($service_id))
          ->save();

        parent::submitForm($form, $form_state);
      }
    }
  }

  /**
   * Helper function defining wording type options.
   */
  protected function wordingTypeOptions() {
    $options = [
      'none' => $this->t('No wording'),
      'default' => $this->t('Default'),
      'entity' => $this->t('Per entity type'),
      'provider' => $this->t('Per entity type and field'),
    ];

    return $options;
  }

}
