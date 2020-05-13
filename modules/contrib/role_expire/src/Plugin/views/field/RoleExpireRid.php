<?php

namespace Drupal\role_expire\Plugin\views\field;

use Drupal\views\ViewExecutable;
use Drupal\role_expire\RoleExpireApiService;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\PrerenderList;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display the role expire data.
 *
 * @ingroup views_field_handlers
 *
 * @see Drupal\user\Plugin\views\field\Roles
 *
 * @ViewsField("role_expire_rid")
 */
class RoleExpireRid extends PrerenderList {

  /**
   * Role expire API service.
   *
   * @var \Drupal\role_expire\RoleExpireApiService
   */
  protected $roleExpireApi;

  /**
   * Constructs a \Drupal\user\Plugin\views\field\Roles object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\role_expire\RoleExpireApiService $role_expire_api
   *   Role expire API service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RoleExpireApiService $role_expire_api) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->roleExpireApi = $role_expire_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('role_expire.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['uid'] = ['table' => 'users_field_data', 'field' => 'uid'];
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    $this->addAdditionalFields();
    $this->field_alias = $this->aliases['uid'];
  }

  /**
   * @{inheritdoc}
   */
  public function preRender(&$values) {
    $this->items = [];

    if (is_array($values)) {
      foreach ($values as $user) {
        $expirations = $this->roleExpireApi->getAllUserRecords($user->uid);
        foreach ($expirations as $role => $timestamp) {
          $this->items[$user->uid][$role]['role'] = $role;
        }
      }
    }
  }

  /**
   * @{inheritdoc}
   */
  public function render_item($count, $item) {
    return $item['role'];
  }

}
