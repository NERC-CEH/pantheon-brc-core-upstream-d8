<?php

/**
 * @file
 * Low-level install the 'entity_legal' module for testing.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Enable the 'entity_legal' module.
$extensions = unserialize($connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField());
$extensions['module']['entity_legal'] = 0;
$connection->update('config')
  ->fields(['data' => serialize($extensions)])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute();
$connection->insert('key_value')
  ->fields(['collection', 'name', 'value'])
  ->values([
    'collection' => 'system.schema',
    'name' => 'entity_legal',
    'value' => 's:4:"8100";',
  ])->execute();

$connection->insert('key_value')
  ->fields(['collection', 'name', 'value'])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'entity_legal_document.entity_type',
    'value' => 'O:42:"Drupal\Core\Config\Entity\ConfigEntityType":44:{s:16:"' . chr(0) . '*' . chr(0) . 'config_prefix";s:8:"document";s:15:"' . chr(0) . '*' . chr(0) . 'static_cache";b:0;s:14:"' . chr(0) . '*' . chr(0) . 'lookup_keys";a:1:{i:0;s:4:"uuid";}s:16:"' . chr(0) . '*' . chr(0) . 'config_export";a:0:{}s:21:"' . chr(0) . '*' . chr(0) . 'mergedConfigExport";a:0:{}s:15:"' . chr(0) . '*' . chr(0) . 'render_cache";b:1;s:19:"' . chr(0) . '*' . chr(0) . 'persistent_cache";b:1;s:14:"' . chr(0) . '*' . chr(0) . 'entity_keys";a:8:{s:2:"id";s:2:"id";s:5:"label";s:5:"label";s:8:"revision";s:0:"";s:6:"bundle";s:0:"";s:8:"langcode";s:8:"langcode";s:16:"default_langcode";s:16:"default_langcode";s:29:"revision_translation_affected";s:29:"revision_translation_affected";s:4:"uuid";s:4:"uuid";}s:5:"' . chr(0) . '*' . chr(0) . 'id";s:21:"entity_legal_document";s:16:"' . chr(0) . '*' . chr(0) . 'originalClass";s:46:"Drupal\entity_legal\Entity\EntityLegalDocument";s:11:"' . chr(0) . '*' . chr(0) . 'handlers";a:4:{s:6:"access";s:59:"Drupal\entity_legal\EntityLegalDocumentAccessControlHandler";s:12:"list_builder";s:50:"Drupal\entity_legal\EntityLegalDocumentListBuilder";s:4:"form";a:3:{s:3:"add";s:48:"Drupal\entity_legal\Form\EntityLegalDocumentForm";s:4:"edit";s:48:"Drupal\entity_legal\Form\EntityLegalDocumentForm";s:6:"delete";s:35:"Drupal\Core\Entity\EntityDeleteForm";}s:7:"storage";s:45:"Drupal\Core\Config\Entity\ConfigEntityStorage";}s:19:"' . chr(0) . '*' . chr(0) . 'admin_permission";s:23:"administer entity legal";s:25:"' . chr(0) . '*' . chr(0) . 'permission_granularity";s:11:"entity_type";s:8:"' . chr(0) . '*' . chr(0) . 'links";a:4:{s:11:"delete-form";s:60:"/admin/structure/legal/manage/{entity_legal_document}/delete";s:9:"edit-form";s:53:"/admin/structure/legal/manage/{entity_legal_document}";s:10:"collection";s:22:"/admin/structure/legal";s:9:"canonical";s:39:"/legal/document/{entity_legal_document}";}s:17:"' . chr(0) . '*' . chr(0) . 'label_callback";N;s:21:"' . chr(0) . '*' . chr(0) . 'bundle_entity_type";N;s:12:"' . chr(0) . '*' . chr(0) . 'bundle_of";s:29:"entity_legal_document_version";s:15:"' . chr(0) . '*' . chr(0) . 'bundle_label";N;s:13:"' . chr(0) . '*' . chr(0) . 'base_table";N;s:22:"' . chr(0) . '*' . chr(0) . 'revision_data_table";N;s:17:"' . chr(0) . '*' . chr(0) . 'revision_table";N;s:13:"' . chr(0) . '*' . chr(0) . 'data_table";N;s:11:"' . chr(0) . '*' . chr(0) . 'internal";b:0;s:15:"' . chr(0) . '*' . chr(0) . 'translatable";b:0;s:19:"' . chr(0) . '*' . chr(0) . 'show_revision_ui";b:0;s:8:"' . chr(0) . '*' . chr(0) . 'label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:14:"Legal document";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:19:"' . chr(0) . '*' . chr(0) . 'label_collection";s:0:"";s:17:"' . chr(0) . '*' . chr(0) . 'label_singular";s:0:"";s:15:"' . chr(0) . '*' . chr(0) . 'label_plural";s:0:"";s:14:"' . chr(0) . '*' . chr(0) . 'label_count";a:0:{}s:15:"' . chr(0) . '*' . chr(0) . 'uri_callback";N;s:8:"' . chr(0) . '*' . chr(0) . 'group";s:13:"configuration";s:14:"' . chr(0) . '*' . chr(0) . 'group_label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:13:"Configuration";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:1:{s:7:"context";s:17:"Entity type group";}}s:22:"' . chr(0) . '*' . chr(0) . 'field_ui_base_route";N;s:26:"' . chr(0) . '*' . chr(0) . 'common_reference_target";b:0;s:22:"' . chr(0) . '*' . chr(0) . 'list_cache_contexts";a:0:{}s:18:"' . chr(0) . '*' . chr(0) . 'list_cache_tags";a:1:{i:0;s:33:"config:entity_legal_document_list";}s:14:"' . chr(0) . '*' . chr(0) . 'constraints";a:0:{}s:13:"' . chr(0) . '*' . chr(0) . 'additional";a:1:{s:10:"token_type";s:21:"entity_legal_document";}s:8:"' . chr(0) . '*' . chr(0) . 'class";s:46:"Drupal\entity_legal\Entity\EntityLegalDocument";s:11:"' . chr(0) . '*' . chr(0) . 'provider";s:12:"entity_legal";s:14:"' . chr(0) . '*' . chr(0) . '_serviceIds";a:0:{}s:18:"' . chr(0) . '*' . chr(0) . '_entityStorages";a:0:{}s:20:"' . chr(0) . '*' . chr(0) . 'stringTranslation";N;}',
  ])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'entity_legal_document_acceptance.entity_type',
    'value' => 'O:36:"Drupal\Core\Entity\ContentEntityType":42:{s:25:"' . chr(0) . '*' . chr(0) . 'revision_metadata_keys";a:1:{s:16:"revision_default";s:16:"revision_default";}s:31:"' . chr(0) . '*' . chr(0) . 'requiredRevisionMetadataKeys";a:1:{s:16:"revision_default";s:16:"revision_default";}s:15:"' . chr(0) . '*' . chr(0) . 'static_cache";b:1;s:15:"' . chr(0) . '*' . chr(0) . 'render_cache";b:1;s:19:"' . chr(0) . '*' . chr(0) . 'persistent_cache";b:1;s:14:"' . chr(0) . '*' . chr(0) . 'entity_keys";a:7:{s:2:"id";s:3:"aid";s:3:"uid";s:3:"uid";s:8:"revision";s:0:"";s:6:"bundle";s:0:"";s:8:"langcode";s:0:"";s:16:"default_langcode";s:16:"default_langcode";s:29:"revision_translation_affected";s:29:"revision_translation_affected";}s:5:"' . chr(0) . '*' . chr(0) . 'id";s:32:"entity_legal_document_acceptance";s:16:"' . chr(0) . '*' . chr(0) . 'originalClass";s:56:"Drupal\entity_legal\Entity\EntityLegalDocumentAcceptance";s:11:"' . chr(0) . '*' . chr(0) . 'handlers";a:4:{s:7:"storage";s:46:"Drupal\Core\Entity\Sql\SqlContentEntityStorage";s:10:"views_data";s:28:"Drupal\views\EntityViewsData";s:6:"access";s:45:"Drupal\Core\Entity\EntityAccessControlHandler";s:12:"view_builder";s:36:"Drupal\Core\Entity\EntityViewBuilder";}s:19:"' . chr(0) . '*' . chr(0) . 'admin_permission";s:23:"administer entity legal";s:25:"' . chr(0) . '*' . chr(0) . 'permission_granularity";s:11:"entity_type";s:8:"' . chr(0) . '*' . chr(0) . 'links";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'label_callback";N;s:21:"' . chr(0) . '*' . chr(0) . 'bundle_entity_type";N;s:12:"' . chr(0) . '*' . chr(0) . 'bundle_of";N;s:15:"' . chr(0) . '*' . chr(0) . 'bundle_label";N;s:13:"' . chr(0) . '*' . chr(0) . 'base_table";s:32:"entity_legal_document_acceptance";s:22:"' . chr(0) . '*' . chr(0) . 'revision_data_table";N;s:17:"' . chr(0) . '*' . chr(0) . 'revision_table";N;s:13:"' . chr(0) . '*' . chr(0) . 'data_table";N;s:11:"' . chr(0) . '*' . chr(0) . 'internal";b:0;s:15:"' . chr(0) . '*' . chr(0) . 'translatable";b:0;s:19:"' . chr(0) . '*' . chr(0) . 'show_revision_ui";b:0;s:8:"' . chr(0) . '*' . chr(0) . 'label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:25:"Legal document acceptance";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:19:"' . chr(0) . '*' . chr(0) . 'label_collection";s:0:"";s:17:"' . chr(0) . '*' . chr(0) . 'label_singular";s:0:"";s:15:"' . chr(0) . '*' . chr(0) . 'label_plural";s:0:"";s:14:"' . chr(0) . '*' . chr(0) . 'label_count";a:0:{}s:15:"' . chr(0) . '*' . chr(0) . 'uri_callback";N;s:8:"' . chr(0) . '*' . chr(0) . 'group";s:7:"content";s:14:"' . chr(0) . '*' . chr(0) . 'group_label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:7:"Content";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:1:{s:7:"context";s:17:"Entity type group";}}s:22:"' . chr(0) . '*' . chr(0) . 'field_ui_base_route";N;s:26:"' . chr(0) . '*' . chr(0) . 'common_reference_target";b:0;s:22:"' . chr(0) . '*' . chr(0) . 'list_cache_contexts";a:0:{}s:18:"' . chr(0) . '*' . chr(0) . 'list_cache_tags";a:1:{i:0;s:37:"entity_legal_document_acceptance_list";}s:14:"' . chr(0) . '*' . chr(0) . 'constraints";a:1:{s:26:"EntityUntranslatableFields";N;}s:13:"' . chr(0) . '*' . chr(0) . 'additional";a:1:{s:10:"token_type";s:32:"entity_legal_document_acceptance";}s:8:"' . chr(0) . '*' . chr(0) . 'class";s:56:"Drupal\entity_legal\Entity\EntityLegalDocumentAcceptance";s:11:"' . chr(0) . '*' . chr(0) . 'provider";s:12:"entity_legal";s:14:"' . chr(0) . '*' . chr(0) . '_serviceIds";a:0:{}s:18:"' . chr(0) . '*' . chr(0) . '_entityStorages";a:0:{}s:20:"' . chr(0) . '*' . chr(0) . 'stringTranslation";N;}',
  ])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'entity_legal_document_acceptance.field_storage_definitions',
    'value' => 'a:5:{s:3:"aid";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:7:"integer";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:4:"size";s:6:"normal";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:2;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:18:"field_item:integer";s:8:"settings";a:6:{s:8:"unsigned";b:1;s:4:"size";s:6:"normal";s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"prefix";s:0:"";s:6:"suffix";s:0:"";}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:9:"Entity ID";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:32:"The entity ID of this agreement.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:9:"read-only";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:3:"aid";s:11:"entity_type";s:32:"entity_legal_document_acceptance";s:6:"bundle";N;s:13:"initial_value";N;}}s:21:"document_version_name";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:16:"entity_reference";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:9:"target_id";a:3:{s:11:"description";s:28:"The ID of the target entity.";s:4:"type";s:13:"varchar_ascii";s:6:"length";i:255;}}s:7:"indexes";a:1:{s:9:"target_id";a:1:{i:0;s:9:"target_id";}}s:11:"unique keys";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:40;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:27:"field_item:entity_reference";s:8:"settings";a:3:{s:11:"target_type";s:29:"entity_legal_document_version";s:7:"handler";s:7:"default";s:16:"handler_settings";a:0:{}}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:16:"Document version";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:61:"The name of the document version this acceptance is bound to.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:8:"required";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:21:"document_version_name";s:11:"entity_type";s:32:"entity_legal_document_acceptance";s:6:"bundle";N;s:13:"initial_value";N;}}s:3:"uid";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:16:"entity_reference";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:9:"target_id";a:3:{s:11:"description";s:28:"The ID of the target entity.";s:4:"type";s:3:"int";s:8:"unsigned";b:1;}}s:7:"indexes";a:1:{s:9:"target_id";a:1:{i:0;s:9:"target_id";}}s:11:"unique keys";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:77;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:27:"field_item:entity_reference";s:8:"settings";a:3:{s:11:"target_type";s:4:"user";s:7:"handler";s:7:"default";s:16:"handler_settings";a:0:{}}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:9:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:6:"Author";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:29:"The author of the acceptance.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:22:"default_value_callback";s:74:"Drupal\entity_legal\Entity\EntityLegalDocumentAcceptance::getCurrentUserId";s:8:"required";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:3:"uid";s:11:"entity_type";s:32:"entity_legal_document_acceptance";s:6:"bundle";N;s:13:"initial_value";N;}}s:15:"acceptance_date";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:7:"created";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:1:{s:4:"type";s:3:"int";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:115;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:18:"field_item:created";s:8:"settings";a:0:{}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:7:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:13:"Date accepted";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:35:"The date the document was accepted.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:15:"acceptance_date";s:11:"entity_type";s:32:"entity_legal_document_acceptance";s:6:"bundle";N;s:13:"initial_value";N;}}s:4:"data";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:11:"string_long";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:2:{s:4:"type";s:4:"text";s:4:"size";s:3:"big";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:144;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:22:"field_item:string_long";s:8:"settings";a:1:{s:14:"case_sensitive";b:0;}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:4:"Data";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";s:47:"A dump of user data to help verify acceptances.";s:22:"default_value_callback";s:65:"Drupal\entity_legal\Entity\EntityLegalDocumentAcceptance::getData";s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:4:"data";s:11:"entity_type";s:32:"entity_legal_document_acceptance";s:6:"bundle";N;s:13:"initial_value";N;}}}',
  ])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'entity_legal_document_version.entity_type',
    'value' => 'O:36:"Drupal\Core\Entity\ContentEntityType":42:{s:25:"' . chr(0) . '*' . chr(0) . 'revision_metadata_keys";a:1:{s:16:"revision_default";s:16:"revision_default";}s:31:"' . chr(0) . '*' . chr(0) . 'requiredRevisionMetadataKeys";a:1:{s:16:"revision_default";s:16:"revision_default";}s:15:"' . chr(0) . '*' . chr(0) . 'static_cache";b:1;s:15:"' . chr(0) . '*' . chr(0) . 'render_cache";b:1;s:19:"' . chr(0) . '*' . chr(0) . 'persistent_cache";b:1;s:14:"' . chr(0) . '*' . chr(0) . 'entity_keys";a:8:{s:2:"id";s:4:"name";s:5:"label";s:5:"label";s:8:"langcode";s:8:"langcode";s:4:"uuid";s:4:"uuid";s:6:"bundle";s:13:"document_name";s:8:"revision";s:0:"";s:16:"default_langcode";s:16:"default_langcode";s:29:"revision_translation_affected";s:29:"revision_translation_affected";}s:5:"' . chr(0) . '*' . chr(0) . 'id";s:29:"entity_legal_document_version";s:16:"' . chr(0) . '*' . chr(0) . 'originalClass";s:53:"Drupal\entity_legal\Entity\EntityLegalDocumentVersion";s:11:"' . chr(0) . '*' . chr(0) . 'handlers";a:5:{s:6:"access";s:45:"Drupal\Core\Entity\EntityAccessControlHandler";s:7:"storage";s:46:"Drupal\Core\Entity\Sql\SqlContentEntityStorage";s:12:"view_builder";s:57:"Drupal\entity_legal\EntityLegalDocumentVersionViewBuilder";s:10:"views_data";s:28:"Drupal\views\EntityViewsData";s:4:"form";a:1:{s:7:"default";s:55:"Drupal\entity_legal\Form\EntityLegalDocumentVersionForm";}}s:19:"' . chr(0) . '*' . chr(0) . 'admin_permission";s:23:"administer entity legal";s:25:"' . chr(0) . '*' . chr(0) . 'permission_granularity";s:11:"entity_type";s:8:"' . chr(0) . '*' . chr(0) . 'links";a:1:{s:9:"canonical";s:71:"/legal/document/{entity_legal_document}/{entity_legal_document_version}";}s:17:"' . chr(0) . '*' . chr(0) . 'label_callback";N;s:21:"' . chr(0) . '*' . chr(0) . 'bundle_entity_type";s:21:"entity_legal_document";s:12:"' . chr(0) . '*' . chr(0) . 'bundle_of";N;s:15:"' . chr(0) . '*' . chr(0) . 'bundle_label";N;s:13:"' . chr(0) . '*' . chr(0) . 'base_table";s:29:"entity_legal_document_version";s:22:"' . chr(0) . '*' . chr(0) . 'revision_data_table";N;s:17:"' . chr(0) . '*' . chr(0) . 'revision_table";N;s:13:"' . chr(0) . '*' . chr(0) . 'data_table";s:34:"entity_legal_document_version_data";s:11:"' . chr(0) . '*' . chr(0) . 'internal";b:0;s:15:"' . chr(0) . '*' . chr(0) . 'translatable";b:1;s:19:"' . chr(0) . '*' . chr(0) . 'show_revision_ui";b:0;s:8:"' . chr(0) . '*' . chr(0) . 'label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:22:"Legal document version";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:19:"' . chr(0) . '*' . chr(0) . 'label_collection";s:0:"";s:17:"' . chr(0) . '*' . chr(0) . 'label_singular";s:0:"";s:15:"' . chr(0) . '*' . chr(0) . 'label_plural";s:0:"";s:14:"' . chr(0) . '*' . chr(0) . 'label_count";a:0:{}s:15:"' . chr(0) . '*' . chr(0) . 'uri_callback";N;s:8:"' . chr(0) . '*' . chr(0) . 'group";s:7:"content";s:14:"' . chr(0) . '*' . chr(0) . 'group_label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:7:"Content";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:1:{s:7:"context";s:17:"Entity type group";}}s:22:"' . chr(0) . '*' . chr(0) . 'field_ui_base_route";N;s:26:"' . chr(0) . '*' . chr(0) . 'common_reference_target";b:0;s:22:"' . chr(0) . '*' . chr(0) . 'list_cache_contexts";a:0:{}s:18:"' . chr(0) . '*' . chr(0) . 'list_cache_tags";a:1:{i:0;s:34:"entity_legal_document_version_list";}s:14:"' . chr(0) . '*' . chr(0) . 'constraints";a:1:{s:26:"EntityUntranslatableFields";N;}s:13:"' . chr(0) . '*' . chr(0) . 'additional";a:1:{s:10:"token_type";s:29:"entity_legal_document_version";}s:8:"' . chr(0) . '*' . chr(0) . 'class";s:53:"Drupal\entity_legal\Entity\EntityLegalDocumentVersion";s:11:"' . chr(0) . '*' . chr(0) . 'provider";s:12:"entity_legal";s:14:"' . chr(0) . '*' . chr(0) . '_serviceIds";a:0:{}s:18:"' . chr(0) . '*' . chr(0) . '_entityStorages";a:0:{}s:20:"' . chr(0) . '*' . chr(0) . 'stringTranslation";N;}',
  ])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'entity_legal_document_version.field_storage_definitions',
    'value' => 'a:9:{s:4:"name";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:6:"string";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:64;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:2;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:17:"field_item:string";s:8:"settings";a:4:{s:10:"max_length";i:64;s:8:"is_ascii";b:0;s:14:"case_sensitive";b:0;s:8:"unsigned";b:1;}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:9:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:4:"Name";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:31:"The entity ID of this document.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:9:"read-only";b:1;s:22:"default_value_callback";s:69:"Drupal\entity_legal\Entity\EntityLegalDocumentVersion::getDefaultName";s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:4:"name";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}s:8:"langcode";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:8:"language";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:2:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:12;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:39;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:19:"field_item:language";s:8:"settings";a:0:{}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:8:"Language";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:35:"The document version language code.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:12:"translatable";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:8:"langcode";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}s:4:"uuid";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:4:"uuid";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:128;s:6:"binary";b:0;}}s:11:"unique keys";a:1:{s:5:"value";a:1:{i:0;s:5:"value";}}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:70;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:15:"field_item:uuid";s:8:"settings";a:3:{s:10:"max_length";i:128;s:8:"is_ascii";b:1;s:14:"case_sensitive";b:0;}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:4:"UUID";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:32:"The entity UUID of this document";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:9:"read-only";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:4:"uuid";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}s:13:"document_name";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:16:"entity_reference";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:9:"target_id";a:3:{s:11:"description";s:28:"The ID of the target entity.";s:4:"type";s:13:"varchar_ascii";s:6:"length";i:32;}}s:7:"indexes";a:1:{s:9:"target_id";a:1:{i:0;s:9:"target_id";}}s:11:"unique keys";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:107;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:27:"field_item:entity_reference";s:8:"settings";a:3:{s:11:"target_type";s:21:"entity_legal_document";s:7:"handler";s:7:"default";s:16:"handler_settings";a:0:{}}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:7:"Form ID";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:50:"The name of the document this version is bound to.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:8:"required";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:13:"document_name";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}s:5:"label";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:6:"string";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:144;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:17:"field_item:string";s:8:"settings";a:3:{s:10:"max_length";i:255;s:8:"is_ascii";b:0;s:14:"case_sensitive";b:0;}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:9:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:5:"Label";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:26:"The title of the document.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:12:"translatable";b:1;s:8:"required";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:5:"label";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}s:16:"acceptance_label";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:6:"string";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:180;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:17:"field_item:string";s:8:"settings";a:3:{s:10:"max_length";i:255;s:8:"is_ascii";b:0;s:14:"case_sensitive";b:0;}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:16:"Acceptance label";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:12:"translatable";b:1;s:8:"required";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:16:"acceptance_label";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}s:7:"created";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:7:"created";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:1:{s:4:"type";s:3:"int";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:212;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:18:"field_item:created";s:8:"settings";a:0:{}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:9:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:7:"Created";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:34:"The date the document was created.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:12:"translatable";b:1;s:8:"required";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:7:"created";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}s:7:"changed";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:7:"changed";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:1:{s:4:"type";s:3:"int";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:243;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:18:"field_item:changed";s:8:"settings";a:0:{}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:9:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:7:"Changed";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:34:"The date the document was changed.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:12:"translatable";b:1;s:8:"required";b:1;s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:7:"changed";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}s:16:"default_langcode";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:"' . chr(0) . '*' . chr(0) . 'type";s:7:"boolean";s:9:"' . chr(0) . '*' . chr(0) . 'schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:2:{s:4:"type";s:3:"int";s:4:"size";s:4:"tiny";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:"' . chr(0) . '*' . chr(0) . 'indexes";a:0:{}s:17:"' . chr(0) . '*' . chr(0) . 'itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:"' . chr(0) . '*' . chr(0) . 'fieldDefinition";r:274;s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:2:{s:4:"type";s:18:"field_item:boolean";s:8:"settings";a:2:{s:8:"on_label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:2:"On";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:9:"off_label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:3:"Off";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}}}}s:13:"' . chr(0) . '*' . chr(0) . 'definition";a:10:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:19:"Default translation";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:"' . chr(0) . '*' . chr(0) . 'string";s:58:"A flag indicating whether this is the default translation.";s:12:"' . chr(0) . '*' . chr(0) . 'arguments";a:0:{}s:10:"' . chr(0) . '*' . chr(0) . 'options";a:0:{}}s:12:"translatable";b:1;s:12:"revisionable";b:1;s:13:"default_value";a:1:{i:0;a:1:{s:5:"value";b:1;}}s:8:"provider";s:12:"entity_legal";s:10:"field_name";s:16:"default_langcode";s:11:"entity_type";s:29:"entity_legal_document_version";s:6:"bundle";N;s:13:"initial_value";N;}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_acceptance.entity_schema_data',
    'value' => 'a:1:{s:32:"entity_legal_document_acceptance";a:1:{s:11:"primary key";a:1:{i:0;s:3:"aid";}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_acceptance.field_schema_data.acceptance_date',
    'value' => 'a:1:{s:32:"entity_legal_document_acceptance";a:1:{s:6:"fields";a:1:{s:15:"acceptance_date";a:2:{s:4:"type";s:3:"int";s:8:"not null";b:0;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_acceptance.field_schema_data.aid',
    'value' => 'a:1:{s:32:"entity_legal_document_acceptance";a:1:{s:6:"fields";a:1:{s:3:"aid";a:4:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:4:"size";s:6:"normal";s:8:"not null";b:1;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_acceptance.field_schema_data.data',
    'value' => 'a:1:{s:32:"entity_legal_document_acceptance";a:1:{s:6:"fields";a:1:{s:4:"data";a:3:{s:4:"type";s:4:"text";s:4:"size";s:3:"big";s:8:"not null";b:0;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_acceptance.field_schema_data.document_version_name',
    'value' => 'a:1:{s:32:"entity_legal_document_acceptance";a:2:{s:6:"fields";a:1:{s:21:"document_version_name";a:4:{s:11:"description";s:28:"The ID of the target entity.";s:4:"type";s:13:"varchar_ascii";s:6:"length";i:255;s:8:"not null";b:0;}}s:7:"indexes";a:1:{s:44:"entity_legal_document_acceptance__1f9c8bfb41";a:1:{i:0;s:21:"document_version_name";}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_acceptance.field_schema_data.uid',
    'value' => 'a:1:{s:32:"entity_legal_document_acceptance";a:2:{s:6:"fields";a:1:{s:3:"uid";a:4:{s:11:"description";s:28:"The ID of the target entity.";s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:8:"not null";b:1;}}s:7:"indexes";a:1:{s:44:"entity_legal_document_acceptance__e0ce9ef25e";a:1:{i:0;s:3:"uid";}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.entity_schema_data',
    'value' => 'a:2:{s:29:"entity_legal_document_version";a:1:{s:11:"primary key";a:1:{i:0;s:4:"name";}}s:34:"entity_legal_document_version_data";a:2:{s:11:"primary key";a:2:{i:0;s:4:"name";i:1;s:8:"langcode";}s:7:"indexes";a:1:{s:61:"entity_legal_document_version__id__default_langcode__langcode";a:3:{i:0;s:4:"name";i:1;s:16:"default_langcode";i:2;s:8:"langcode";}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.acceptance_label',
    'value' => 'a:1:{s:34:"entity_legal_document_version_data";a:1:{s:6:"fields";a:1:{s:16:"acceptance_label";a:4:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;s:8:"not null";b:0;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.changed',
    'value' => 'a:1:{s:34:"entity_legal_document_version_data";a:1:{s:6:"fields";a:1:{s:7:"changed";a:2:{s:4:"type";s:3:"int";s:8:"not null";b:0;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.created',
    'value' => 'a:1:{s:34:"entity_legal_document_version_data";a:1:{s:6:"fields";a:1:{s:7:"created";a:2:{s:4:"type";s:3:"int";s:8:"not null";b:0;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.default_langcode',
    'value' => 'a:1:{s:34:"entity_legal_document_version_data";a:1:{s:6:"fields";a:1:{s:16:"default_langcode";a:3:{s:4:"type";s:3:"int";s:4:"size";s:4:"tiny";s:8:"not null";b:1;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.document_name',
    'value' => 'a:2:{s:29:"entity_legal_document_version";a:2:{s:6:"fields";a:1:{s:13:"document_name";a:4:{s:11:"description";s:28:"The ID of the target entity.";s:4:"type";s:13:"varchar_ascii";s:6:"length";i:32;s:8:"not null";b:1;}}s:7:"indexes";a:1:{s:41:"entity_legal_document_version__1a41277600";a:1:{i:0;s:13:"document_name";}}}s:34:"entity_legal_document_version_data";a:2:{s:6:"fields";a:1:{s:13:"document_name";a:4:{s:11:"description";s:28:"The ID of the target entity.";s:4:"type";s:13:"varchar_ascii";s:6:"length";i:32;s:8:"not null";b:1;}}s:7:"indexes";a:1:{s:41:"entity_legal_document_version__1a41277600";a:1:{i:0;s:13:"document_name";}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.label',
    'value' => 'a:1:{s:34:"entity_legal_document_version_data";a:1:{s:6:"fields";a:1:{s:5:"label";a:4:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;s:8:"not null";b:0;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.langcode',
    'value' => 'a:2:{s:29:"entity_legal_document_version";a:1:{s:6:"fields";a:1:{s:8:"langcode";a:3:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:12;s:8:"not null";b:1;}}}s:34:"entity_legal_document_version_data";a:1:{s:6:"fields";a:1:{s:8:"langcode";a:3:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:12;s:8:"not null";b:1;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.name',
    'value' => 'a:2:{s:29:"entity_legal_document_version";a:1:{s:6:"fields";a:1:{s:4:"name";a:4:{s:4:"type";s:7:"varchar";s:6:"length";i:64;s:6:"binary";b:0;s:8:"not null";b:1;}}}s:34:"entity_legal_document_version_data";a:1:{s:6:"fields";a:1:{s:4:"name";a:4:{s:4:"type";s:7:"varchar";s:6:"length";i:64;s:6:"binary";b:0;s:8:"not null";b:1;}}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'entity_legal_document_version.field_schema_data.uuid',
    'value' => 'a:1:{s:29:"entity_legal_document_version";a:2:{s:6:"fields";a:1:{s:4:"uuid";a:4:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:128;s:6:"binary";b:0;s:8:"not null";b:1;}}s:11:"unique keys";a:1:{s:48:"entity_legal_document_version_field__uuid__value";a:1:{i:0;s:4:"uuid";}}}}',
  ])
  ->execute();

$connection->schema()->createTable('entity_legal_document_acceptance', [
  'fields' => [
    'aid' => [
      'type' => 'serial',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ],
    'document_version_name' => [
      'type' => 'varchar_ascii',
      'not null' => FALSE,
      'length' => '255',
    ],
    'uid' => [
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ],
    'acceptance_date' => [
      'type' => 'int',
      'not null' => FALSE,
      'size' => 'normal',
    ],
    'data' => [
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
    ],
  ],
  'primary key' => [
    'aid',
  ],
  'indexes' => [
    'entity_legal_document_acceptance__1f9c8bfb41' => [
      'document_version_name',
    ],
    'entity_legal_document_acceptance__e0ce9ef25e' => [
      'uid',
    ],
  ],
  'mysql_character_set' => 'utf8mb4',
]);

$connection->schema()->createTable('entity_legal_document_version', [
  'fields' => [
    'name' => [
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '64',
    ],
    'document_name' => [
      'type' => 'varchar_ascii',
      'not null' => TRUE,
      'length' => '32',
    ],
    'uuid' => [
      'type' => 'varchar_ascii',
      'not null' => TRUE,
      'length' => '128',
    ],
    'langcode' => [
      'type' => 'varchar_ascii',
      'not null' => TRUE,
      'length' => '12',
    ],
  ],
  'primary key' => [
    'name',
  ],
  'unique keys' => [
    'entity_legal_document_version_field__uuid__value' => [
      'uuid',
    ],
  ],
  'indexes' => [
    'entity_legal_document_version__1a41277600' => [
      'document_name',
    ],
  ],
  'mysql_character_set' => 'utf8mb4',
]);

$connection->schema()->createTable('entity_legal_document_version_data', [
  'fields' => [
    'name' => [
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '64',
    ],
    'document_name' => [
      'type' => 'varchar_ascii',
      'not null' => TRUE,
      'length' => '32',
    ],
    'langcode' => [
      'type' => 'varchar_ascii',
      'not null' => TRUE,
      'length' => '12',
    ],
    'label' => [
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => '255',
    ],
    'acceptance_label' => [
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => '255',
    ],
    'created' => [
      'type' => 'int',
      'not null' => FALSE,
      'size' => 'normal',
    ],
    'changed' => [
      'type' => 'int',
      'not null' => FALSE,
      'size' => 'normal',
    ],
    'default_langcode' => [
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'tiny',
    ],
  ],
  'primary key' => [
    'name',
    'langcode',
  ],
  'indexes' => [
    'entity_legal_document_version__id__default_langcode__langcode' => [
      'name',
      'default_langcode',
      'langcode',
    ],
    'entity_legal_document_version__1a41277600' => [
      'document_name',
    ],
  ],
  'mysql_character_set' => 'utf8mb4',
]);
