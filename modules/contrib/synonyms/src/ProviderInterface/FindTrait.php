<?php

namespace Drupal\synonyms\ProviderInterface;

use Drupal\Core\Database\Query\ConditionInterface;

/**
 * Supportive trait to find synonyms.
 */
trait FindTrait {

  /**
   * Supportive method to process $condition argument in synonymsFind().
   *
   * This method will swap FindInterface::COLUMN_* to real
   * column names in $condition for you, so you do not have to worry about
   * internal processing of $condition object.
   *
   * @param \Drupal\Core\Database\Query\ConditionInterface $condition
   *   Condition to be processed.
   * @param string $synonym_column
   *   Actual name of the column where synonyms are kept in text.
   * @param string $entity_id_column
   *   Actual name of the column where entity_ids are kept.
   */
  public function synonymsFindProcessCondition(ConditionInterface $condition, $synonym_column, $entity_id_column) {
    $condition_array = &$condition->conditions();
    foreach ($condition_array as &$v) {
      if (is_array($v) && isset($v['field'])) {
        if ($v['field'] instanceof ConditionInterface) {
          // Recursively process this condition too.
          $this->synonymsFindProcessCondition($v['field'], $synonym_column, $entity_id_column);
        }
        else {
          $replace = [
            FindInterface::COLUMN_SYNONYM_PLACEHOLDER => $synonym_column,
            FindInterface::COLUMN_ENTITY_ID_PLACEHOLDER => $entity_id_column,
          ];
          $v['field'] = str_replace(array_keys($replace), array_values($replace), $v['field']);
        }
      }
    }
  }

  /**
   * Look up entities by their synonyms within a behavior implementation.
   *
   * You are provided with a SQL condition that you should apply to the storage
   * of synonyms within the provided behavior implementation. And then return
   * result: what entities are matched by the provided condition through what
   * synonyms.
   *
   * @param \Drupal\Core\Database\Query\ConditionInterface $condition
   *   Condition that defines what to search for. Apart from normal SQL
   *   conditions as known in Drupal, it may contain the following placeholders:
   *   - FindInterface::COLUMN_SYNONYM_PLACEHOLDER: to denote
   *     synonyms column which you should replace with the actual column name
   *     where the synonyms data for your provider is stored in plain text.
   *   - FindInterface::COLUMN_ENTITY_ID_PLACEHOLDER: to denote
   *     column that holds entity ID. You are supposed to replace this
   *     placeholder with actual column name that holds entity ID in your case.
   *   For ease of work with these placeholders, you may use the
   *   FindTrait and then just invoke the
   *   $this->synonymsFindProcessCondition() method, so you won't have to worry
   *   much about it.
   *
   * @return \Traversable
   *   Traversable result set of found synonyms and entity IDs to which those
   *   belong. Each element in the result set should be an object and should
   *   have the following structure:
   *   - synonym: (string) Synonym that was found and which satisfies the
   *     provided condition
   *   - entity_id: (int) ID of the entity to which the found synonym belongs
   */
  abstract public function synonymsFind(ConditionInterface $condition);

}
