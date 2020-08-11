<?php

namespace Drupal\role_expire\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\role_expire\RoleExpireApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure order for this site.
 */
class RoleExpireConfigurationForm extends ConfigFormBase {

  /**
   * Role expire API service.
   *
   * @var \Drupal\role_expire\RoleExpireApiService
   */
  protected $roleExpireApi;

  /**
   * {@inheritdoc}
   */
  public function __construct(RoleExpireApiService $roleExpireApi) {
    $this->roleExpireApi = $roleExpireApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      // Load the services required to construct this class.
      $container->get('role_expire.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'role_expire_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'role_expire.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $excluded_roles = ['anonymous', 'authenticated'];

    $parsed_roles = [];
    $roles = user_roles();
    foreach ($roles as $role) {
      $parsed_roles[$role->id()] = $role->label();
    }

    $values = $this->roleExpireApi->getRolesAfterExpiration();
    $valuesStatus = $this->roleExpireApi->getRolesExpirationStatus();

    $default = [
      0 => $this->t('- None -'),
    ];
    // It is important to respect the keys on this array merge.
    $roles_select = $default + $parsed_roles;
    unset($roles_select['anonymous']);
    unset($roles_select['authenticated']);

    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General settings'),
      '#weight' => 1,
    ];

    $form['general']['role_after'] = [
      '#type' => 'details',
      '#title' => $this->t('Role assignment after expiration'),
      '#weight' => 1,
      '#open' => TRUE,
    ];

    foreach ($parsed_roles as $rid => $role_name) {
      if (!in_array($rid, $excluded_roles)) {
        $form['general']['role_after'][$rid] = [
          '#type' => 'select',
          '#options' => $roles_select,
          '#title' => $this->t('Role to assign after the role ":r" expires', [':r' => $role_name]),
          '#default_value' => isset($values[$rid]) ? $values[$rid] : 0,
        ];
      }
    }

    $form['general']['disabled_role'] = [
      '#type' => 'details',
      '#title' => $this->t('Role expiration scope'),
      '#weight' => 1,
      '#open' => TRUE,
    ];

    foreach ($parsed_roles as $rid => $role_name) {
      if (!in_array($rid, $excluded_roles)) {
        $form['general']['disabled_role']['disable_' . $rid] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Disable role expiration for :r', [':r' => $role_name]),
          '#default_value' => isset($valuesStatus[$rid]) ? $valuesStatus[$rid] : 1,
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $excluded_roles = ['anonymous', 'authenticated'];

    $data = [];
    $dataDisabled = [];
    $parsed_roles = [];
    $roles = user_roles();
    foreach ($roles as $role) {
      $parsed_roles[$role->id()] = $role->label();
    }
    foreach ($parsed_roles as $rid => $role_name) {
      if (!in_array($rid, $excluded_roles)) {
        $data[$rid] = $values[$rid];
        $dataDisabled[$rid] = $values['disable_' . $rid];
      }
    }

    $this->config('role_expire.config')
      ->set('role_expire_default_roles', json_encode($data))
      ->save();
    $this->config('role_expire.config')
      ->set('role_expire_disabled_roles', json_encode($dataDisabled))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
