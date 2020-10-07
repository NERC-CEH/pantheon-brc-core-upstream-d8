<?php

namespace Drupal\commerce_paypal\Plugin\views\area;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a PayPal Credit messaging area handler.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("commerce_paypal_credit_messaging")
 */
class CreditMessaging extends AreaPluginBase {

  /**
   * The order storage.
   *
   * @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage
   */
  protected $orderStorage;

  /**
   * Constructs a new OrderTotal instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->orderStorage = $entity_type_manager->getStorage('commerce_order');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['placement'] = ['default' => 'cart'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['empty']['#description'] = $this->t("Even if selected, this area handler will never render if a valid order cannot be found in the View's arguments.");

    $form['placement'] = [
      '#type' => 'radios',
      '#title' => $this->t('Placement type'),
      '#description' => $this->t('<b>Warning:</b> this area handler cannot be used on any page with PayPal Smart Payment Buttons or other JavaScript elements; it will always be excluded from the checkout review page.'),
      '#options' => [
        'cart' => $this->t('Cart'),
        'payment' => $this->t('Payment'),
      ],
      '#default_value' => $this->options['placement'] ?? 'cart',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    // Do not attempt to render this on the checkout review page where we know
    // the PayPal JS SDK will be included for payment buttons.
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);

    if ($path_args[1] == 'checkout' && $path_args[3] == 'review') {
      return [];
    }

    if (!$empty || !empty($this->options['empty'])) {
      foreach ($this->view->argument as $name => $argument) {
        // First look for an order_id argument.
        if (!$argument instanceof NumericArgument) {
          continue;
        }
        if (!in_array($argument->getField(), ['commerce_order.order_id', 'commerce_order_item.order_id'])) {
          continue;
        }
        /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
        if ($order = $this->orderStorage->load($argument->getValue())) {
          $amount = $order->get('total_price')->getValue()[0]['number'];
          $markup = '<div data-pp-message data-pp-placement="cart" data-pp-amount="' . $amount . '" data-pp-style-layout="text" data-pp-style-logo-type="alternative"></div>';

          $element = [
            '#type' => 'markup',
            '#markup' => $markup,
          ];

          // Add Credit Messaging JS to the block.
          $element['#attached']['library'][] = 'commerce_paypal/credit_messaging';
          return $element;
        }
      }
    }
    return [];
  }

}
