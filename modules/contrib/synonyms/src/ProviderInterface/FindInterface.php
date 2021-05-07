<?php

namespace Drupal\synonyms\ProviderInterface;

use Drupal\Core\Database\Query\ConditionInterface;

/**
 * Interface to look up entities by synonyms they have.
 */
interface FindInterface {

  /**
   * Constant which denotes placeholder of a synonym column.
   *
   * @var string
   */
  const COLUMN_SYNONYM_PLACEHOLDER = '***SYNONYM_COLUMN***';

  /**
   * Constant which denotes placeholder of an entity ID column.
   *
   * @var string
   */
  const COLUMN_ENTITY_ID_PLACEHOLDER = '***ENTITY_ID***';

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
  public function synonymsFindProcessCondition(ConditionInterface $condition, $synonym_column, $entity_id_column);

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
  public function synonymsFind(ConditionInterface $condition);

}
