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
    $table_prefix = '<strong>Wording type: </strong>' . \Drupal::config('synonyms.settings')->get('wording_type_label') . '.<br>';
    if (\Drupal::config('synonyms.settings')->get('wording_type') != 'none') {
      if ($widget_services = $this->behaviorService->getWidgetServices()) {
        $table_prefix .= '<br><strong>Default wordings: </strong><br>';
        foreach ($widget_services as $service_id => $service) {
          $wording = \Drupal::config('synonyms_' . $service_id . '.widget')->get('default_wording');
          if (empty($wording)) {
            $wording = '<strong>Notice: </strong>This wording is empty. Please, edit settings and add wording here if you need it.';
          }
          $table_prefix .= $service->getWidgetTitle() . ' widget: ' . $wording . '<br>';
        }
      }
    }
    $table_prefix .= '<br>';
    $render = [
      '#prefix' => '<div>' . $table_prefix . '</div>',
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
            if ($this->behaviorService->serviceIsEnabled($entity_type->id(), $bundle, $service_id)) {
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

          $render[] = [
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
