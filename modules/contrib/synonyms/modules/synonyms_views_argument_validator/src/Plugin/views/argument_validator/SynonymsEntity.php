<?php

namespace Drupal\synonyms_views_argument_validator\Plugin\views\argument_validator;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\synonyms\ProviderInterface\FindInterface;
use Drupal\views\Plugin\views\argument_validator\Entity;

/**
 * Synonyms-friendly entity validator.
 *
 * @ViewsArgumentValidator(
 *   id = "synonyms_entity",
 *   deriver = "Drupal\synonyms_views_argument_validator\Plugin\Derivative\ViewsSynonymsEntityArgumentValidator"
 * )
 */
class SynonymsEntity extends Entity {

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['transform'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Transform dashes in URL to spaces.'),
      '#default_value' => $this->options['transform'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateArgument($argument) {
    if ($this->options['transform']) {
      $argument = str_replace('-', ' ', $argument);
    }

    $entity_type = $this->entityTypeManager->getDefinition($this->definition['entity_type']);

    if ($entity_type->hasKey('label') || $entity_type->id() == 'user') {
      $query = $this->entityTypeManager->getStorage($entity_type->id())->getQuery();

      // User entity type does not declare its label, while it does have one.
      $label_column = $entity_type->id() == 'user' ? 'name' : $entity_type->getKey('label');

      $query->condition($label_column, $argument, '=');

      if ($entity_type->hasKey('bundle') && !empty($this->options['bundles'])) {
        $query->condition($entity_type->getKey('bundle'), $this->options['bundles'], 'IN');
      }

      $result = $query->execute();
      if (!empty($result)) {
        $entities = $this->entityTypeManager->getStorage($entity_type->id())->loadMultiple($result);
        foreach ($entities as $entity) {
          if ($this->validateEntity($entity)) {
            $this->argument->argument = $entity->id();
            return TRUE;
          }
        }
      }
    }

    // We've fallen through with search by entity name, now it's time to search
    // by synonyms.
    $condition = new Condition('AND');
    $condition->condition(FindInterface::COLUMN_SYNONYM_PLACEHOLDER, $argument, '=');

    foreach (\Drupal::service('synonyms.provider_service')->findSynonyms($condition, $entity_type, empty($this->options['bundles']) ? NULL : $this->options['bundles']) as $synonym) {
      $entity = $this->entityTypeManager->getStorage($entity_type->id())->load($synonym->entity_id);
      if ($this->validateEntity($entity)) {
        $this->argument->argument = $entity->id();
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['transform'] = ['default' => FALSE];

    return $options;
  }

}
