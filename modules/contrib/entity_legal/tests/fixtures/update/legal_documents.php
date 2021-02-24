<?php

/**
 * @file
 * Fixture file that adds two legal documents: 'legal_notice', 'provacy_policy'.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Add the legal document config entities.
$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'entity_legal.document.legal_notice',
    'data' => 'a:10:{s:4:"uuid";s:36:"f25d275b-4dc6-46c5-ac92-bfa14da0d994";s:8:"langcode";s:2:"en";s:6:"status";b:1;s:12:"dependencies";a:0:{}s:2:"id";s:12:"legal_notice";s:5:"label";s:12:"Legal notice";s:17:"published_version";s:23:"legal_notice_1562867323";s:14:"require_signup";b:0;s:16:"require_existing";b:0;s:8:"settings";a:2:{s:9:"new_users";a:2:{s:7:"require";b:0;s:14:"require_method";s:11:"form_inline";}s:14:"existing_users";a:2:{s:7:"require";b:0;s:14:"require_method";s:5:"popup";}}}',
  ])
  ->values([
    'collection' => '',
    'name' => 'entity_legal.document.privacy_policy',
    'data' => 'a:10:{s:4:"uuid";s:36:"82e0c895-0146-49bc-b376-961d6657982f";s:8:"langcode";s:2:"en";s:6:"status";b:1;s:12:"dependencies";a:0:{}s:2:"id";s:14:"privacy_policy";s:5:"label";s:14:"Privacy policy";s:17:"published_version";s:25:"privacy_policy_1562867356";s:14:"require_signup";b:0;s:16:"require_existing";b:0;s:8:"settings";a:2:{s:9:"new_users";a:2:{s:7:"require";b:0;s:14:"require_method";s:11:"form_inline";}s:14:"existing_users";a:2:{s:7:"require";b:0;s:14:"require_method";s:5:"popup";}}}',
  ])
  ->execute();
