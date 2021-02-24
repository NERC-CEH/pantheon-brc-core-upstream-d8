<?php

/**
 * @file
 * Post-update scripts for Entity Legal module.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\entity_legal\EntityLegalDocumentInterface;

/**
 * Allow customizing the legal document title.
 */
function entity_legal_post_update_title_pattern(array &$sandbox) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, ENTITY_LEGAL_DOCUMENT_ENTITY_NAME, function (EntityLegalDocumentInterface $document) {
    $settings = $document->get('settings');
    $settings['title_pattern'] = '[entity_legal_document:published-version:label]';
    $document->set('settings', $settings);
    return TRUE;
  });
}
