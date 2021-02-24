<?php

/**
 * @file
 * API documentation for entity_legal module.
 */

/**
 * Alter available user notification methods.
 *
 * @param array $methods
 *   Available methods.
 */
function hook_entity_legal_document_method_alter(array $methods) {
  $methods['existing_users']['email'] = t('Email all users');
}
