<?php

namespace Drupal\commerce_paypal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CreditMessagingForm
 *
 * @package Drupal\commerce_paypal\Form
 */
class CreditMessagingForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return [
      'commerce_paypal.settings'
    ];
  }

  public function getFormId() {
    return 'commerce_paypal_credit_messaging_form';
  }

  /**
   * Settings form.
   *
   * @return form
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('commerce_paypal.settings');

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PayPal Client ID'),
      '#description' => $this->t('You must supply a PayPal client ID for messaging to appear where you have enabled it.'),
      '#default_value' => $config->get('commerce_paypal.credit_messaging_client_id'),
      '#required' => FALSE
    ];

    $form['add_to_cart'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable PayPal Credit messaging on Add to Cart forms.'),
      '#default_value' => $config->get('commerce_paypal.credit_messaging_add_to_cart')
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Submit and save the form values.
   *
   * @return form
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('commerce_paypal.settings');
    $config->set('commerce_paypal.credit_messaging_client_id', $form_state->getValue('client_id'));
    $config->set('commerce_paypal.credit_messaging_add_to_cart', $form_state->getValue('add_to_cart'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
