<?php

namespace Drupal\synonyms_ui\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\synonyms\ProviderPluginManager;
use Drupal\synonyms\Plugin\Synonyms\Provider\ProviderConfigurableInterface;

/**
 * Entity form for 'synonym' config entity type.
 */
class SynonymForm extends EntityForm {

  /**
   * The synonym entity.
   *
   * @var \Drupal\synonyms\SynonymInterface
   */
  protected $entity;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The synonyms provider plugin manager.
   *
   * @var \Drupal\synonyms\ProviderPluginManager
   */
  protected $synonymsProviderPluginManager;

  /**
   * Entity type that is being edited/added.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $controlledEntityType;

  /**
   * Bundle that is being edited/added.
   *
   * @var string
   */
  protected $controlledBundle;

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * SynonymForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\synonyms\ProviderPluginManager $synonyms_provider_plugin_manager
   *   The synonyms provider plugin_manager.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, ProviderPluginManager $synonyms_provider_plugin_manager, ContainerInterface $container) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->synonymsProviderPluginManager = $synonyms_provider_plugin_manager;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('plugin.manager.synonyms_provider'),
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function init(FormStateInterface $form_state) {
    parent::init($form_state);

    if ($this->entity->isNew()) {
      $this->controlledEntityType = $this->getRequest()->get('synonyms_entity_type')->id();
      $this->controlledBundle = $this->getRequest()->get('bundle');
    }
    else {
      $plugin_definition = $this->entity->getProviderPluginInstance()->getPluginDefinition();
      $this->controlledEntityType = $plugin_definition['controlled_entity_type'];
      $this->controlledBundle = $plugin_definition['controlled_bundle'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $class = get_class($this);

    $provider_plugin = $this->entity->getProviderPlugin();
    if ($form_state->getValue('provider_plugin')) {
      $provider_plugin = $form_state->getValue('provider_plugin');
    }

    $form['id'] = [
      '#type' => 'value',
      '#value' => str_replace(':', '.', $provider_plugin),
    ];

    $options = [];
    foreach ($this->synonymsProviderPluginManager->getDefinitions() as $plugin_id => $plugin) {
      if ($plugin['controlled_entity_type'] == $this->controlledEntityType && $plugin['controlled_bundle'] == $this->controlledBundle) {
        $options[$plugin_id] = $plugin['label'];
      }
    }

    $form['provider_plugin'] = [
      '#type' => 'select',
      '#title' => $this->t('Synonyms provider'),
      '#description' => $this->t('Select what synonyms provider it should represent.'),
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => $this->entity->getProviderPlugin(),
      '#ajax' => [
        'wrapper' => 'synonyms-entity-configuration-ajax-wrapper',
        'event' => 'change',
        'callback' => [$class, 'ajaxForm'],
      ],
    ];

    $form['ajax_wrapper'] = [
      '#prefix' => '<div id="synonyms-entity-configuration-ajax-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['ajax_wrapper']['provider_configuration'] = [
      '#tree' => TRUE,
      '#title' => $this->t('Provider settings'),
      '#open' => TRUE,
    ];

    if ($provider_plugin) {
      $provider_plugin_instance = $this->entity->getProviderPluginInstance();
      if ($provider_plugin_instance instanceof ProviderConfigurableInterface && $this->showWordingForm()) {
        $form['ajax_wrapper']['provider_configuration']['#type'] = 'details';
        $form['ajax_wrapper']['provider_configuration'] += $provider_plugin_instance->buildConfigurationForm($form['ajax_wrapper']['provider_configuration'], $form_state, $this->entity->getProviderConfiguration(), $this->entity);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $provider = $this->entity->getProviderPluginInstance();
    if ($provider instanceof ProviderConfigurableInterface && $this->showWordingForm()) {
      $provider->validateConfigurationForm($form['ajax_wrapper']['provider_configuration'], $this->getSubFormState('provider_configuration', $form, $form_state), $this->entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $provider = $this->entity->getProviderPluginInstance();
    if ($provider instanceof ProviderConfigurableInterface && $this->showWordingForm()) {
      $provider_configuration = $provider->submitConfigurationForm($form['ajax_wrapper']['provider_configuration'], $this->getSubFormState('provider_configuration', $form, $form_state), $this->entity);
      $this->entity->setProviderConfiguration($provider_configuration);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();

    if ($status) {
      $this->messenger()->addStatus($this->t('Saved the %label synonym configuration.', [
        '%label' => $this->entity->label(),
      ]));
    }
    else {
      $this->messenger()->addError($this->t('The %label synonym configuration was not saved.', [
        '%label' => $this->entity->label(),
      ]));
    }

    $form_state->setRedirect('synonym.entity_type.bundle', [
      'synonyms_entity_type' => $this->controlledEntityType,
      'bundle' => $this->controlledBundle,
    ]);
  }

  /**
   * Check whether entity with such ID already exists.
   *
   * @param string $id
   *   Entity ID to check.
   *
   * @return bool
   *   Whether entity with such ID already exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('synonym')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * Ajax callback.
   */
  public static function ajaxForm(array &$form, FormStateInterface $form_state, Request $request) {
    return $form['ajax_wrapper'];
  }

  /**
   * Supportive method to create sub-form-states.
   *
   * @param string $element_name
   *   Name of the nested form element for which to create a sub form state.
   * @param array $form
   *   Full form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Full form state out of which to create sub form state.
   *
   * @return \Drupal\Core\Form\SubformState
   *   Sub form state object generated based on the input arguments
   */
  protected function getSubFormState($element_name, array $form, FormStateInterface $form_state) {
    return SubformState::createForSubform($form['ajax_wrapper'][$element_name], $form, $form_state);
  }

  /**
   * Helper function which return depends on wording type.
   *
   * @return bool
   *   Whether wording forms should be visible or hidden.
   */
  public function showWordingForm() {
    return \Drupal::config('synonyms.settings')->get('wording_type') == 'provider';
  }

}
