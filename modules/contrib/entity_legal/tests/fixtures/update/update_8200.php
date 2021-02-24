<?php

/**
 * @file
 * Fixture file for testing entity_legal_update_8200().
 *
 * @see entity_legal_update_8200()
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

$connection->insert('entity_legal_document_version')
  ->fields([
    'name',
    'document_name',
    'uuid',
    'langcode',
  ])
  ->values([
    'name' => 'legal_notice_1562867301',
    'document_name' => 'legal_notice',
    'uuid' => '04031419-5142-418e-9ebd-afac61cfcf4a',
    'langcode' => 'en',
  ])
  ->values([
    'name' => 'legal_notice_1562867311',
    'document_name' => 'legal_notice',
    'uuid' => '7bddeaca-5bc4-4589-bed3-76f03238b44c',
    'langcode' => 'en',
  ])
  ->values([
    'name' => 'legal_notice_1562867323',
    'document_name' => 'legal_notice',
    'uuid' => 'fdb0e094-a2a8-48ee-87df-47648ef2afa8',
    'langcode' => 'en',
  ])
  ->values([
    'name' => 'privacy_policy_1562867346',
    'document_name' => 'privacy_policy',
    'uuid' => '8e29678c-ae62-46b6-9e6f-c6232c6953d2',
    'langcode' => 'en',
  ])
  ->values([
    'name' => 'privacy_policy_1562867356',
    'document_name' => 'privacy_policy',
    'uuid' => '4be19452-054e-4b96-aea0-f40c42b71042',
    'langcode' => 'en',
  ])
  ->execute();

$connection->insert('entity_legal_document_version_data')
  ->fields([
    'name',
    'document_name',
    'langcode',
    'label',
    'acceptance_label',
    'created',
    'changed',
    'default_langcode',
  ])
  ->values([
    'name' => 'legal_notice_1562867301',
    'document_name' => 'legal_notice',
    'langcode' => 'en',
    'label' => 'v1',
    'acceptance_label' => 'I agree to the <a href="[entity_legal_document:url]">Legal notice</a> document',
    'created' => '1562867307',
    'changed' => '1562867307',
    'default_langcode' => '1',
  ])
  ->values([
    'name' => 'legal_notice_1562867311',
    'document_name' => 'legal_notice',
    'langcode' => 'en',
    'label' => 'v2',
    'acceptance_label' => 'I agree to the <a href="[entity_legal_document:url]">Legal notice</a> document',
    'created' => '1562867315',
    'changed' => '1562867315',
    'default_langcode' => '1',
  ])
  ->values([
    'name' => 'legal_notice_1562867323',
    'document_name' => 'legal_notice',
    'langcode' => 'en',
    'label' => 'v3',
    'acceptance_label' => 'I agree to the <a href="[entity_legal_document:url]">Legal notice</a> document',
    'created' => '1562867328',
    'changed' => '1562867328',
    'default_langcode' => '1',
  ])
  ->values([
    'name' => 'privacy_policy_1562867346',
    'document_name' => 'privacy_policy',
    'langcode' => 'en',
    'label' => 'v7',
    'acceptance_label' => 'I agree to the <a href="[entity_legal_document:url]">Privacy policy</a> document',
    'created' => '1562867350',
    'changed' => '1562867350',
    'default_langcode' => '1',
  ])
  ->values([
    'name' => 'privacy_policy_1562867356',
    'document_name' => 'privacy_policy',
    'langcode' => 'en',
    'label' => 'v8',
    'acceptance_label' => 'I agree to the <a href="[entity_legal_document:url]">Privacy policy</a> document',
    'created' => '1562867359',
    'changed' => '1562867359',
    'default_langcode' => '1',
  ])
  ->execute();
