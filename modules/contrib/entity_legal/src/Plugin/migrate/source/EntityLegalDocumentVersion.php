<?php

namespace Drupal\entity_legal\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal entity legal document version migration source plugin.
 *
 * @MigrateSource(
 *   id = "entity_legal_document_version",
 *   source_provider = "entity_legal"
 * )
 */
class EntityLegalDocumentVersion extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('entity_legal_document_version', 'v')->fields('v');
    $query->join('entity_legal_document', 'd', 'v.document_name = d.name');
    $query->addExpression('IF(d.published_version = v.vid, 1, 0)', 'published');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name' => $this->t('The entity ID of this document.'),
      'document_name' => $this->t('The name of the document this version is bound to.'),
      'label' => $this->t('The title of the document.'),
      'published' => $this->t('Published'),
      'acceptance_label' => $this->t('Acceptance label'),
      'created' => $this->t('The date the document was created.'),
      'updated' => $this->t('The date the document was changed.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['name']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get Field API field values.
    foreach (array_keys($this->getFields('entity_legal_document_version', $row->getSourceProperty('document_name'))) as $field) {
      $name = $row->getSourceProperty('vid');
      $row->setSourceProperty($field, $this->getFieldValues('entity_legal_document_version', $field, $name));
    }
    return parent::prepareRow($row);
  }

}
