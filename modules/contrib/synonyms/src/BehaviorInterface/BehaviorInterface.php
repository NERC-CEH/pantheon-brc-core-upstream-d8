<?php

namespace Drupal\synonyms\BehaviorInterface;

/**
 * Interface of a synonyms behavior. All behaviors must implement it.
 */
interface BehaviorInterface {

  /**
   * Get machine readable ID of this behavior.
   *
   * @return string
   *   The return ID
   */
  public function getId();

  /**
   * Get human readable title of this behavior.
   *
   * @return string
   *   The return title
   */
  public function getTitle();

}
