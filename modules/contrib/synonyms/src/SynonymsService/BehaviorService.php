<?php

namespace Drupal\synonyms\SynonymsService;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\synonyms\BehaviorInterface\BehaviorInterface;
use Drupal\synonyms\BehaviorInterface\WidgetInterface;

/**
 * Collect all known synonyms behavior services.
 *
 * Collect all known synonyms behavior services during dependency injection
 * container compilation.
 */
class BehaviorService implements ContainerInjectionInterface {

  /**
   * Collected behavior services.
   *
   * @var array
   */
  protected $behaviorServices;

  /**
   * Collected widget services.
   *
   * @var array
   */
  protected $widgetServices;

  /**
   * BehaviorService constructor.
   */
  public function __construct() {
    $this->behaviorServices = [];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Add a new discovered behavior service.
   *
   * @param \Drupal\synonyms\BehaviorInterface\BehaviorInterface $behavior_service
   *   Behavior service object that was discovered and should be added into the
   *   list of known ones.
   * @param string $id
   *   Service ID that corresponds to this behavior service.
   */
  public function addBehaviorService(BehaviorInterface $behavior_service, $id) {
    // It is more convenient to use machine readable IDs as array keys here.
    $machine_id = $behavior_service->getId();
    // Behavior services collector.
    if (!isset($this->behaviorServices[$machine_id])) {
      $this->behaviorServices[$machine_id] = $behavior_service;
    }
    // Widget services collector.
    if ($behavior_service instanceof WidgetInterface && !isset($this->widgetServices[$machine_id])) {
      $this->widgetServices[$machine_id] = $behavior_service;
    }
  }

  /**
   * Array of known behavior services.
   *
   * @return array
   *   The return value
   */
  public function getBehaviorServices() {
    return $this->behaviorServices;
  }

  /**
   * Array of known widget services.
   *
   * @return array
   *   The return value
   */
  public function getWidgetServices() {
    return $this->widgetServices;
  }

}
