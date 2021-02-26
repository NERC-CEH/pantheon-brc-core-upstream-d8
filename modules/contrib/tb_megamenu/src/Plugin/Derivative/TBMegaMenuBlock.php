<?php

namespace Drupal\tb_megamenu\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides blocks which belong to TB Mega Menu.
 */
class TBMegaMenuBlock extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * TBMegaMenuBlock constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory interface.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $menus = menu_ui_get_menus();
    foreach ($this->configFactory->listAll('tb_megamenu.menu_config.') as $index_id) {
      $info = $this->configFactory->get($index_id);
      $menu = $info->get('menu');
      if (isset($menus[$menu])) {
        $this->derivatives[$menu] = $base_plugin_definition;
        $this->derivatives[$menu]['admin_label'] = $menus[$menu];
      }
    }
    return $this->derivatives;
  }

}
