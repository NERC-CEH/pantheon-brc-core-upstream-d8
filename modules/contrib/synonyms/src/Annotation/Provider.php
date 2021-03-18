<?php

namespace Drupal\synonyms\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for synonyms provider plugin instance.
 *
 * @Annotation
 */
class Provider extends Plugin {

  /**
   * Machine readable name of this plugin.
   *
   * @var string
   */
  public $id;

  /**
   * Human readable name of this plugin.
   *
   * @var string
   */
  public $label;

  /**
   * Entity type which is controlled by ths plugin.
   *
   * Entity type into which this plugin provides synonyms.
   *
   * @var string
   */
  public $controlled_entity_type;

  /**
   * Bundle which is controlled by this plugin.
   *
   * Bundle into which this plugin provides synonyms. If the entity type does
   * not support bundles, just put here the entity type.
   *
   * @var string
   */
  public $controlled_bundle;

  /**
   * The field which provides synonyms.
   *
   * @var string
   */
  public $field;

}
