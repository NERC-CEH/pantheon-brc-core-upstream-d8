<?php

namespace Drupal\synonyms_list_field\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Synonyms list field settings form.
 */
class SettingsForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'synonyms_list_field_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['synonyms_list_field.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // The 'Include entity label' checkbox.
    $form['include_entity_label'] = [
      '#type' => 'checkbox',
      '#title' => t('Include entity label'),
      '#default_value' => $this->config('synonyms_list_field.settings')->get('include_entity_label'),
      '#description' => $this->t('If checked, the entity label is prepended to computed "Synonyms list" field.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('synonyms_list_field.settings')
      ->set('include_entity_label', $form_state->getValue('include_entity_label'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
