<?php

use \Drupal\Core\File\FileSystemInterface;

/**
 * @file
 * Drush integration for tmgmt_file.
 */

/**
 * Implements hook_drush_command().
 */
function tmgmt_file_drush_command() {
  $items = array();

  $items['tmgmt_translate_import'] = array(
    'description' => 'Import XLIFF translation files',
    'arguments' => array(
      'name'    => 'Directory path that is search for *.xlf files or a file name',
    ),
    'aliases' => array('tmti'),
  );

  return $items;
}

/**
 * Import XLIFF files from a directory or single file.
 */
function drush_tmgmt_file_tmgmt_translate_import($name = NULL) {
  if (!$name) {
    return drush_set_error(dt('You need to provide a directory path or filename.'));
  }

  if (!file_exists($name)) {
    // Drush changes the current working directory to the drupal root directory.
    // Also check the current directory.
    if (!file_exists(drush_cwd() . '/' . $name)) {
      return drush_set_error(dt('@name does not exists or is not accessible.', array('@name' => $name)));
    }
    else {
      // The path is relative to the current directory, update the variable.
      $name = drush_cwd() . '/' . $name;
    }
  }

  if (is_dir($name)) {
    drush_log(dt('Scanning dir @dir.', array('@dir' => $name)), 'success');
    $files = FileSystemInterface::scanDirectory($name, '/.*\.xlf$/');
    if (empty($files)) {
      drush_set_error(dt('No files found to import in @name.', array('@name' => $name)));
    }
  }
  else {
    // Create the structure expected by the loop below.
    $files = array($name => (object)array('name' => basename($name)));
  }

  $plugin = \Drupal::service('plugin.manager.tmgmt_file.format')->createInstance('xlf');
  foreach ($files as $path => $info) {
    $job = $plugin->validateImport($path);
    if (empty($job)) {
      drush_log(dt('No translation job found for @filename.', array('@filename' => $info->name)), 'error');
      continue;
    }

    if ($job->isFinished()) {
      drush_log(dt('Skipping @filename for finished job @name (#@id).', array('@filename' => $info->name, '@name' => $job->label(), '@id' => $job->id())), 'warning');
      continue;
    }

    try {
      // Validation successful, start import.
      $job->addTranslatedData($plugin->import($path));
      drush_log(dt('Successfully imported file @filename for translation job @name (#@id).', array('@filename' => $info->name, '@name' => $job->label(), '@id' => $job->id())), 'success');
    }
    catch (Exception $e) {
      drush_log(dt('Failed importing file @filename: @error', array('@filename' => $info->name, '@error' => $e->getMessage())), 'error');
    }
  }
}
?>
