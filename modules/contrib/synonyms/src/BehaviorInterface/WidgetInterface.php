<?php

namespace Drupal\synonyms\BehaviorInterface;

/**
 * Interface of a synonyms widget. All widgets must implement it.
 */
interface WidgetInterface {

  /**
   * Get human readable title of this widget.
   *
   * @return string
   *   The return title
   */
  public function getWidgetTitle();

}
