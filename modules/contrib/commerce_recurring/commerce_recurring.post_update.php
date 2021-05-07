<?php

/**
 * @file
 * Post update functions for Commerce Recurring.
 */

/**
 * Sets the next_renewal field on existing active subscriptions.
 */
function commerce_recurring_post_update_1(&$sandbox = NULL) {
  $subscription_storage = \Drupal::entityTypeManager()->getStorage('commerce_subscription');
  if (!isset($sandbox['current_count'])) {
    $query = $subscription_storage->getQuery();
    $query
      ->condition('state', 'active')
      ->accessCheck(FALSE)
      ->notExists('next_renewal');
    $sandbox['total_count'] = $query->count()->execute();
    $sandbox['updated_subscriptions'] = [];
    $sandbox['current_count'] = 0;

    if (empty($sandbox['total_count'])) {
      $sandbox['#finished'] = 1;
      return;
    }
  }
  $query = $subscription_storage->getQuery();
  $query
    ->condition('state', 'active')
    ->accessCheck(FALSE)
    ->notExists('next_renewal')
    ->range(0, 20);

  // Make sure we don't query subscriptions that were already updated.
  if ($sandbox['updated_subscriptions']) {
    $query->condition('subscription_id', $sandbox['updated_subscriptions'], 'NOT IN');
  }

  $subscription_ids = $query->execute();
  if (empty($subscription_ids)) {
    $sandbox['#finished'] = 1;
    return;
  }
  /** @var \Drupal\commerce_recurring\Entity\SubscriptionInterface[] $subscriptions */
  $subscriptions = $subscription_storage->loadMultiple($subscription_ids);
  /** @var \Drupal\commerce_order\OrderStorage $order_storage */
  $order_storage = \Drupal::entityTypeManager()->getStorage('commerce_order');
  foreach ($subscriptions as $subscription) {
    $sandbox['updated_subscriptions'][] = $subscription->id();
    $order_ids = $subscription->getOrderIds();
    if (!$order_ids) {
      continue;
    }
    $current_order_id = end($order_ids);
    // We load the unchanged order to make sure it's not refreshed.
    if ($current_order = $order_storage->loadUnchanged($current_order_id)) {
      /** @var \Drupal\commerce_recurring\BillingPeriod $billing_period */
      $billing_period = $current_order->get('billing_period')->first()->toBillingPeriod();
      $subscription->setNextRenewalTime($billing_period->getEndDate()->getTimestamp());
      $subscription->save();
    }
  }
  $sandbox['current_count'] += count($subscriptions);
  if ($sandbox['current_count'] >= $sandbox['total_count']) {
    $sandbox['#finished'] = 1;
  }
  else {
    $sandbox['#finished'] = ($sandbox['total_count'] - $sandbox['current_count']) / $sandbox['total_count'];
  }
}

/**
 * Add the new 'Subscriptions' view.
 */
function commerce_recurring_post_update_2() {
  /** @var \Drupal\commerce\Config\ConfigUpdaterInterface $config_updater */
  $config_updater = \Drupal::service('commerce.config_updater');
  $result = $config_updater->import([
    'views.view.commerce_subscriptions',
  ]);
  $message = implode('<br>', $result->getFailed());

  return $message;
}

/**
 * Add the new 'User subscriptions' view.
 */
function commerce_recurring_post_update_3() {
  /** @var \Drupal\commerce\Config\ConfigUpdaterInterface $config_updater */
  $config_updater = \Drupal::service('commerce.config_updater');
  $result = $config_updater->import([
    'views.view.commerce_user_subscriptions',
  ]);
  $message = implode('<br>', $result->getFailed());

  return $message;
}

/**
 * Add the new 'Customer' Subscription form mode and displays.
 */
function commerce_recurring_post_update_4() {
  /** @var \Drupal\commerce\Config\ConfigUpdaterInterface $config_updater */
  $config_updater = \Drupal::service('commerce.config_updater');
  $result = $config_updater->import([
    'core.entity_form_mode.commerce_subscription.customer',
    'core.entity_form_display.commerce_subscription.product_variation.customer',
    'core.entity_form_display.commerce_subscription.standalone.customer',
  ]);
  $message = implode('<br>', $result->getFailed());

  return $message;
}

/**
 * Add the new 'Subscription orders (customer)' view and customer facing suscription
 * view displays.
 */
function commerce_recurring_post_update_5() {
 /** @var \Drupal\commerce\Config\ConfigUpdaterInterface $config_updater */
 $config_updater = \Drupal::service('commerce.config_updater');
 $result = $config_updater->import([
    'views.view.commerce_subscription_orders_customer',
    'core.entity_view_mode.commerce_subscription.customer',
    'core.entity_view_display.commerce_subscription.product_variation.customer',
    'core.entity_view_display.commerce_subscription.standalone.customer',
 ]);
 $message = implode('<br>', $result->getFailed());

 return $message;
}

/**
 * Add the new 'Subscription orders (administrator)' view and administrator
 * facing suscription view displays.
 */
function commerce_recurring_post_update_6() {
  /** @var \Drupal\commerce\Config\ConfigUpdaterInterface $config_updater */
  $config_updater = \Drupal::service('commerce.config_updater');
  $result = $config_updater->import([
    'views.view.commerce_subscription_orders_admin',
    'core.entity_view_display.commerce_subscription.product_variation.default',
    'core.entity_view_display.commerce_subscription.standalone.default',
  ]);
  $message = implode('<br>', $result->getFailed());

  return $message;
}
