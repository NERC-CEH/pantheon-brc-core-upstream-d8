<?php

namespace Drupal\synonyms_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\synonyms\ProviderInterface\FormatWordingTrait;
use Drupal\Component\Utility\Html;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\synonyms\SynonymsService\BehaviorService;
use Drupal\synonyms\BehaviorInterface\WidgetInterface;

/**
 * The behavior form for given entity type.
 */
class BehaviorForm extends ConfigFormBase {

  use StringTranslationTrait, FormatWordingTrait;

  /**
   * The behavior configuration.
   *
   * @var \Drupal\synonyms\SynonymInterface
   */
  protected $config;

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
   * The behavior service.
   *
   * @var \Drupal\synonyms\SynonymsService\BehaviorService
   */
  protected $behaviorService;

  /**
   * Entity type that is being managed.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * Bundle that is being managed.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The base for behavior configuration name.
   *
   * @var string
   */
  protected $configNameBase;

  /**
   * Current 'status' form field name.
   *
   * @var string
   */
  protected $statusName;

  /**
   * Current 'wording' form field name.
   *
   * @var string
   */
  protected $wordingName;

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * BehaviorForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\synonyms\SynonymsService\BehaviorService $behavior_service
   *   The behavior service.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, BehaviorService $behavior_service, ContainerInterface $container) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->behaviorService = $behavior_service;
    $this->container = $container;

    $this->entityType = $this->getRequest()->get('synonyms_entity_type')->id();
    $this->bundle = $this->getRequest()->get('bundle');
    $this->configNameBase = 'synonyms.behavior.' . $this->entityType . '.' . $this->bundle;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('synonyms.behavior_service'),
      $container
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'behavior_configuration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    $config_names = [];
    foreach ($this->behaviorService->getBehaviorServices() as $service_id => $service) {
      $config_names[] = $this->getConfigName($service_id);
    }
    return $config_names;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $replacements = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => [],
    ];
    foreach ($this->formatWordingAvailableTokens() as $token => $token_info) {
      $replacements['#items'][] = Html::escape($token) . ': ' . $token_info;
    }
    $replacements = \Drupal::service('renderer')->renderRoot($replacements);

    foreach ($this->behaviorService->getBehaviorServices() as $service_id => $service) {
      $this->setNames($service_id);

      $form[$this->statusName] = [
        '#type' => 'checkbox',
        '#title' => $this->t('@service service', [
          '@service' => $service->getTitle(),
        ]),
        '#default_value' => $this->config($this->getConfigName($service_id))->get('status') ?: 0,
      ];

      if ($service instanceof WidgetInterface && $this->showWordingForm()) {
        $form[$this->wordingName] = [
          '#type' => 'textfield',
          '#title' => $this->t('@widget widget wording:', [
            '@widget' => $service->getWidgetTitle(),
          ]),
          '#default_value' => $this->config($this->getConfigName($service_id))->get('wording') ?: '',
          '#description' => $this->t('Specify the wording with which @widget widget suggestions should be presented. Available replacement tokens are: @replacements If this field is left empty the @widget widget default wording will be used.', [
            '@widget' => $service->getWidgetTitle(),
            '@replacements' => $replacements,
          ]),
          '#states' => [
            'visible' => [
              ':input[name="' . $this->statusName . '"]' => ['checked' => TRUE],
            ],
          ],
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($this->behaviorService->getBehaviorServices() as $service_id => $service) {
      $this->setNames($service_id);
      $this->config($this->getConfigName($service_id))
        ->set('status', $form_state->getValue($this->statusName))
        ->set('wording', $form_state->getValue($this->wordingName))
        ->save();

      parent::submitForm($form, $form_state);
    }
  }

  /**
   * Title callback for 'behavior.entity_type.bundle'.
   */
  public function entityTypeBundleBehaviorsTitle() {
    if ($this->entityType == $this->bundle) {
      return $this->t('Manage behaviors of @entity_type', [
        '@entity_type' => $this->getRequest()->get('synonyms_entity_type')->getLabel(),
      ]);
    }

    $bundle_info = $this->entityTypeBundleInfo->getBundleInfo($this->entityType);

    return $this->t('Manage behaviors of @entity_type @bundle', [
      '@entity_type' => $this->getRequest()->get('synonyms_entity_type')->getLabel(),
      '@bundle' => $bundle_info[$this->bundle]['label'],
    ]);
  }

  /**
   * Helper function to generate form element names.
   */
  public function setNames($service_id) {
    $this->statusName = $service_id . '_status';
    $this->wordingName = $service_id . '_wording';
  }

  /**
   * Helper function to generate form element names.
   */
  public function getConfigName($service_id) {
    return $this->configNameBase . '.' . $service_id;
  }

  /**
   * Helper function which return depends on wording type.
   *
   * @return bool
   *   Whether wording forms should be visible or hidden.
   */
  public function showWordingForm() {
    $wording_type = \Drupal::config('synonyms.settings')->get('wording_type');
    return $wording_type == 'entity' || $wording_type == 'provider';
  }

}
