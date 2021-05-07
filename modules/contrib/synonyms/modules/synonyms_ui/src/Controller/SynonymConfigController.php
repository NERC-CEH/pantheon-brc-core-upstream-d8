<?php

namespace Drupal\synonyms_ui\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\synonyms\SynonymsService\BehaviorService;
use Drupal\synonyms\SynonymsService\ProviderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for admin UI of the module.
 */
class SynonymConfigController extends ControllerBase {

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The synonyms behavior service.
   *
   * @var \Drupal\synonyms\SynonymsService\BehaviorService
   */
  protected $behaviorService;

  /**
   * The synonyms provider service.
   *
   * @var \Drupal\synonyms\SynonymsService\ProviderService
   */
  protected $providerService;

  /**
   * SynonymConfigController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\synonyms\SynonymsService\BehaviorService $behavior_service
   *   The behavior service.
   * @param \Drupal\synonyms\SynonymsService\ProviderService $provider_service
   *   The provider service.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_type_bundle_info, BehaviorService $behavior_service, ProviderService $provider_service) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->behaviorService = $behavior_service;
    $this->providerService = $provider_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('synonyms.behavior_service'),
      $container->get('synonyms.provider_service')
    );
  }

  /**
   * Routing callback: show the overview table of synonyms configuration.
   */
  public function overview() {

    $render = [];

    // The include entity label item.
    if (\Drupal::moduleHandler()->moduleExists('synonyms_list_field')) {
      $include_entity_label = \Drupal::config('synonyms_list_field.settings')->get('include_entity_label') ? $this->t('Yes') : $this->t('No');
      $render['include_entity_label'] = [
        '#type' => 'item',
        '#name' => 'include_entity_label',
        '#title' => $this->t('Include entity label'),
        '#markup' => $include_entity_label,
        '#wrapper_attributes' => [
          'class' => ['container-inline'],
        ],
      ];
    }

    // Wording type item.
    $render['wording_type'] = [
      '#type' => 'item',
      '#name' => 'wording_type',
      '#title' => $this->t('Wording type'),
      '#markup' => \Drupal::config('synonyms.settings')->get('wording_type_label'),
      '#wrapper_attributes' => [
        'class' => ['container-inline'],
      ],
    ];

    // Default wordings item.
    if (\Drupal::config('synonyms.settings')->get('wording_type') != 'none') {
      if ($widget_services = $this->behaviorService->getWidgetServices()) {
        $default_wordings = [];
        foreach ($widget_services as $service_id => $service) {
          $widget_wording = \Drupal::config('synonyms_' . $service_id . '.settings')->get('default_wording');
          if (empty($widget_wording)) {
            $widget_wording = $this->t('Notice: Wording for this widget is empty. Please, edit settings and add wording here if you need it.');
          }
          $default_wordings[] = $this->t('@widget_title widget: @widget_wording', [
            '@widget_title' => $service->getWidgetTitle(),
            '@widget_wording' => $widget_wording,
          ]);
        }
        $wordings_markup = '<ul>';
        foreach ($default_wordings as $default_wording) {
          $wordings_markup .= '<li>' . $default_wording . '</li>';
        }
        $wordings_markup .= '</ul>';

        $render['default_wordings'] = [
          '#type' => 'item',
          '#title' => $this->t('Default wordings:'),
          '#name' => 'default_wordings',
          '#markup' => $wordings_markup,
        ];
      }
    }

    // The overview table.
    $render['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Entity type'),
        $this->t('Bundle'),
        $this->t('Providers'),
        $this->t('Behaviors'),
        $this->t('Actions'),
      ],
    ];

    foreach ($this->entityTypeManager()->getDefinitions() as $entity_type) {
      if ($entity_type instanceof ContentEntityTypeInterface) {
        foreach ($this->entityTypeBundleInfo->getBundleInfo($entity_type->id()) as $bundle => $bundle_info) {

          $providers_list = [];
          foreach ($this->providerService->getSynonymConfigEntities($entity_type->id(), $bundle) as $synonym_config) {
            $providers_list[] = $synonym_config->label();
          }
          $providers_list = implode(', ', $providers_list);
          $behaviors_list = [];
          foreach ($this->behaviorService->getBehaviorServices() as $service_id => $service) {
            if ($this->providerService->serviceIsEnabled($entity_type->id(), $bundle, $service_id)) {
              $behaviors_list[] = $service->getTitle();
            }
          }
          $behaviors_list = implode(', ', $behaviors_list);

          $links = [];
          $links['manage_providers'] = [
            'title' => $this->t('Manage providers'),
            'url' => Url::fromRoute('synonym.entity_type.bundle', [
              'synonyms_entity_type' => $entity_type->id(),
              'bundle' => $bundle,
            ]),
          ];

          $links['manage_behaviors'] = [
            'title' => $this->t('Manage behaviors'),
            'url' => Url::fromRoute('behavior.entity_type.bundle', [
              'synonyms_entity_type' => $entity_type->id(),
              'bundle' => $bundle,
            ]),
          ];

          $render['table'][] = [
            ['#markup' => Html::escape($entity_type->getLabel())],
            ['#markup' => $bundle == $entity_type->id() ? '' : Html::escape($bundle_info['label'])],
            ['#markup' => Html::escape($providers_list)],
            ['#markup' => Html::escape($behaviors_list)],
            ['#type' => 'operations', '#links' => $links],
          ];
        }
      }
    }

    return $render;
  }

  /**
   * Routing callback to overview a particular entity type providers.
   */
  public function entityTypeBundleProviders(EntityTypeInterface $synonyms_entity_type, $bundle) {
    $table = [
      '#type' => 'table',
      '#header' => [
        $this->t('Provider'),
        $this->t('Operations'),
      ],
    ];

    foreach ($this->providerService->getSynonymConfigEntities($synonyms_entity_type->id(), $bundle) as $synonym_config) {
      $table[] = [
        ['#markup' => Html::escape($synonym_config->label())],
        [
          '#type' => 'operations',
          '#links' => $this->entityTypeManager()->getListBuilder($synonym_config->getEntityTypeId())->getOperations($synonym_config),
        ],
      ];
    }

    return $table;
  }

  /**
   * Title callback for 'synonym.entity_type.bundle'.
   */
  public function entityTypeBundleProvidersTitle(EntityTypeInterface $synonyms_entity_type, $bundle) {
    if ($synonyms_entity_type->id() == $bundle) {
      return $this->t('Manage providers of @entity_type', [
        '@entity_type' => $synonyms_entity_type->getLabel(),
      ]);
    }

    $bundle_info = $this->entityTypeBundleInfo->getBundleInfo($synonyms_entity_type->id());

    return $this->t('Manage providers of @entity_type @bundle', [
      '@entity_type' => $synonyms_entity_type->getLabel(),
      '@bundle' => $bundle_info[$bundle]['label'],
    ]);
  }

}
