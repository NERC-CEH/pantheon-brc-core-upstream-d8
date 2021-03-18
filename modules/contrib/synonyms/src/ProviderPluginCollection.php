<?php

namespace Drupal\synonyms;

use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Provides a collection of synonym provider plugins.
 */
class ProviderPluginCollection extends DefaultSingleLazyPluginCollection {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\synonyms\ProviderInterface\ProviderInterface
   *   The return value
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

}
