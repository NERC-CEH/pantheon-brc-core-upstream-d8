<?php

namespace Drupal\entity_legal\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\ConfigFormBaseTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Drupal\path\Plugin\Field\FieldWidget\PathWidget;
use Drupal\path_alias\PathAliasInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for contact form edit forms.
 */
class EntityLegalDocumentForm extends EntityForm implements ContainerInjectionInterface {

  use ConfigFormBaseTrait;

  /**
   * The path alias storage.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $aliasStorage;

  /**
   * The entity legal plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\entity_legal\EntityLegalDocumentInterface
   */
  protected $entity;

  /**
   * The AccountProxy service.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(ContentEntityStorageInterface $alias_storage, PluginManagerInterface $plugin_manager, AccountProxy $currentUser, ModuleHandler $moduleHandler) {
    $this->aliasStorage = $alias_storage;
    $this->pluginManager = $plugin_manager;
    $this->currentUser = $currentUser;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $etm = $container->get('entity_type.manager');
    return new static(
      $etm->getStorage('path_alias'),
      $container->get('plugin.manager.entity_legal'),
      $container->get('current_user'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#title' => $this->t('Administrative label'),
      '#type' => 'textfield',
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine-readable name'),
      '#required' => TRUE,
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\entity_legal\Entity\EntityLegalDocument::load',
      ],
      '#disabled' => !$this->entity->isNew(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
    ];

    if (!in_array($this->operation, ['add', 'clone'])) {
      $versions = $this->entity->getAllVersions();
      if ($this->operation == 'edit' && empty($versions)) {
        \Drupal::messenger()->addWarning(t('No versions for this document have been found. <a href=":add_link">Add a version</a> to use this document.', [
          ':add_link' => Url::fromRoute('entity.entity_legal_document_version.add_form', ['entity_legal_document' => $this->entity->id()])
            ->toString(),
        ]));
      }

      $header = [
        'title' => $this->t('Title'),
        'created' => $this->t('Created'),
        'changed' => $this->t('Updated'),
        'operations' => $this->t('Operations'),
      ];
      $options = [];

      /** @var \Drupal\entity_legal\Entity\EntityLegalDocumentVersion $version */
      $published_version = NULL;
      foreach ($versions as $version) {
        $route_parameters = ['entity_legal_document' => $this->entity->id()];
        // Use the default uri if this version is the current published version.
        if ($version->isPublished()) {
          $published_version = $version->id();
          $route_name = 'entity.entity_legal_document.canonical';
        }
        else {
          $route_name = 'entity.entity_legal_document_version.canonical';
          $route_parameters['entity_legal_document_version'] = $version->id();
        }

        $links['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('entity.entity_legal_document_version.edit_form', [
            'entity_legal_document_version' => $version->id(),
          ]),
        ];
        if ($version->isTranslatable()) {
          try {
            $links['translate'] = [
              'title' => $this->t('Translate'),
              'url' => $version->toUrl('drupal:content-translation-overview'),
            ];
          }
          catch (EntityMalformedException $e) {
          }
        }
        $operations = [
          '#type' => 'operations',
          '#links' => $links,
        ];
        $options[$version->id()] = [
          'title' => Link::createFromRoute($version->label(), $route_name, $route_parameters),
          'created' => $version->getFormattedDate('created'),
          'changed' => $version->getFormattedDate('changed'),
          'operations' => render($operations),
        ];
      }

      // By default just show a simple overview for all entities.
      $form['versions'] = [
        '#type' => 'details',
        '#title' => $this->t('Current version'),
        '#description' => $this->t('The current version users must agree to. If requiring existing users to accept, those users will be prompted if they have not accepted this particular version in the past.'),
        '#open' => TRUE,
        '#tree' => FALSE,
      ];

      $form_state->set('published_version', $published_version);
      $form['versions']['published_version'] = [
        '#type' => 'tableselect',
        '#header' => $header,
        '#options' => $options,
        '#empty' => $this->t('Create a document version to set up a default'),
        '#multiple' => FALSE,
        '#default_value' => $published_version,
      ];
    }

    $form['settings'] = [
      '#type' => 'vertical_tabs',
      '#weight' => 27,
    ];

    $form['new_users'] = [
      '#title' => $this->t('New users'),
      '#description' => $this->t('Visit the <a href=":permissions">permissions</a> page to ensure that users can view the document.', [
        ':permissions' => Url::fromRoute('user.admin_permissions')->toString(),
      ]),
      '#type' => 'details',
      '#group' => 'settings',
      '#parents' => ['settings', 'new_users'],
      '#tree' => TRUE,
    ];

    $form['new_users']['require'] = [
      '#title' => $this->t('Require new users to accept this agreement on signup'),
      '#type' => 'checkbox',
      '#default_value' => $this->entity->get('require_signup'),
    ];

    $form['new_users']['require_method'] = [
      '#title' => $this->t('Present to user as'),
      '#type' => 'select',
      '#options' => $this->getAcceptanceDeliveryMethodOptions('new_users'),
      '#default_value' => $this->entity->getAcceptanceDeliveryMethod(TRUE),
      '#states' => [
        'visible' => [
          ':input[name="settings[new_users][require]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['existing_users'] = [
      '#title' => $this->t('Existing users'),
      '#description' => $this->t('Visit the <a href=":permissions">permissions</a> page to configure which existing users these settings apply to.', [
        ':permissions' => Url::fromRoute('user.admin_permissions')->toString(),
      ]),
      '#type' => 'details',
      '#group' => 'settings',
      '#parents' => ['settings', 'existing_users'],
      '#tree' => TRUE,
    ];

    $form['existing_users']['require'] = [
      '#title' => $this->t('Require existing users to accept this agreement'),
      '#type' => 'checkbox',
      '#default_value' => $this->entity->get('require_existing'),
    ];

    $form['existing_users']['require_method'] = [
      '#title' => $this->t('Present to user as'),
      '#type' => 'select',
      '#options' => $this->getAcceptanceDeliveryMethodOptions('existing_users'),
      '#default_value' => $this->entity->getAcceptanceDeliveryMethod(),
      '#states' => [
        'visible' => [
          ':input[name="settings[existing_users][require]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['title_pattern'] = [
      '#type' => 'details',
      '#title' => $this->t('Title pattern'),
      '#description' => $this->t("Customize how the legal document title appears on the document's main page. You can use tokens to build the title."),
      '#group' => 'settings',
    ];

    $form['title_pattern']['title_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pattern'),
      '#default_value' => $this->entity->get('settings')['title_pattern'],
      '#parents' => ['settings', 'title_pattern'],
      '#required' => TRUE,
    ];

    $form['title_pattern']['token_help'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['entity_legal_document'],
    ];

    $this->formPathSettings($form);

    return $form;
  }

  /**
   * Add path and pathauto settings to an existing legal document form.
   *
   * @param array $form
   *   The Form array.
   */
  protected function formPathSettings(array &$form) {
    if (!$this->moduleHandler->moduleExists('path')) {
      return;
    }

    /** @var \Drupal\path_alias\PathAliasInterface $alias */
    $alias = $this->pathAlias($this->entity->language()->getId());
    $aliasSource = NULL;

    if (!$alias) {
      $aliasSource = !$this->entity->isNew() ? '/' . $this->entity->toUrl()->getInternalPath() : NULL;
    }

    $form['path'] = [
      '#type' => 'details',
      '#title' => $this->t('URL path settings'),
      '#group' => 'settings',
      '#attributes' => ['class' => ['path-form']],
      '#attached' => ['library' => ['path/drupal.path']],
      '#access' => $this->hasAccessToPathAliases(),
      '#weight' => 5,
      '#tree' => TRUE,
      '#element_validate' => [PathWidget::class, 'validateFormElement'],
      '#parents' => ['path', 0],
    ];

    $form['path']['langcode'] = [
      '#type' => 'language_select',
      '#title' => $this->t('Language'),
      '#languages' => LanguageInterface::STATE_ALL,
      '#default_value' => $this->entity->language()->getId(),
    ];

    $form['path']['alias'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL alias'),
      '#default_value' => $alias ? $alias->getAlias() : '',
      '#maxlength' => 255,
      '#description' => $this->t('The alternative URL for this content. Use a relative path. For example, enter "/about" for the about page.'),
    ];

    $form['path']['pid'] = [
      '#type' => 'value',
      '#value' => $alias ? $alias->id() : NULL,
    ];

    $form['path']['source'] = [
      '#type' => 'value',
      '#value' => $alias ? $alias->getPath() : $aliasSource,
    ];
  }

  /**
   *
   */
  protected function pathAliasSource(): ?string {
    if ($this->entity->isNew()) {
      return NULL;
    }
    return '/' . $this->entity->toUrl()->getInternalPath();
  }

  /**
   *
   */
  protected function pathAlias(string $lang_code): ?PathAliasInterface {
    $path = $this->pathAliasSource();
    if (!$path) {
      return NULL;
    }
    /** @var \Drupal\path_alias\PathAliasInterface[] $aliases */
    $aliases = $this->aliasStorage->loadByProperties([
      'langcode' => $lang_code,
      'path' => $path,
    ]);

    return $aliases ? reset($aliases) : NULL;
  }

  /**
   *
   */
  protected function hasAccessToPathAliases(): bool {
    return $this->currentUser->hasPermission('create url aliases')
      || $this->currentUser->hasPermission('administer url aliases');
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this
      ->saveDocument($form, $form_state)
      ->savePathAlias($form, $form_state)
      ->savePublishedVersion($form, $form_state);
  }

  /**
   * @return $this
   */
  protected function saveDocument(array $form, FormStateInterface $form_state) {
    $this->entity
      ->set(
        'require_signup',
        $this->entity->get('settings')['new_users']['require']
      )
      ->set(
        'require_existing',
        $this->entity->get('settings')['existing_users']['require']
      );
    $status = $this->entity->save();
    if ($status == SAVED_NEW) {
      $form_state->setRedirect(
        'entity.entity_legal_document_version.add_form',
        ['entity_legal_document' => $this->entity->id()]);
    }

    $this->messenger()->addStatus($this->t(
      '@type_label @label has been saved',
      [
        '@type_label' => $this->entity->getEntityType()->getLabel(),
        '@label' => $this->entity->label(),
      ]
    ));

    return $this;
  }

  /**
   * @return $this
   */
  protected function savePathAlias(array $form, FormStateInterface $form_state) {
    $values = (array) $form_state->getValue(['path', '0'], []);

    $langCode = $values['langcode'] ?? $this->entity->language()->getId();
    $path = $this->pathAliasSource();
    $alias = $this->pathAlias($langCode);

    $messenger = $this->messenger();

    if (!$alias && !empty($values['alias'])) {
      $alias = $this->aliasStorage->create([
        'langcode' => $langCode,
        'path' => $path,
        'alias' => $values['alias'],
      ]);
      $alias->save();

      $messenger->addStatus($this->t('A new URL alias has been created'));

      return $this;
    }

    if ($alias && empty($values['alias'])) {
      $alias->delete();

      $messenger->addStatus($this->t(
        'URL alias %alias has been deleted',
        ['%alias' => $alias->getAlias()]
      ));

      return $this;
    }

    if ($alias && $alias->getAlias() !== $values['alias']) {
      $alias->setAlias($values['alias'])->save();

      $messenger->addStatus($this->t(
        'URL alias has ben changed to %alias',
        ['%alias' => $alias->getAlias()]
      ));

      return $this;
    }

    return $this;
  }

  /**
   * @return $this
   */
  protected function savePublishedVersion(array $form, FormStateInterface $form_state) {
    $published_version_id = $form_state->getValue('published_version');
    if (!$published_version_id) {
      return $this;
    }

    // Update the published version.
    if ($form_state->get('published_version') && $form_state->get('published_version') !== $form_state->getValue('published_version')) {
      $storage = $this->entityTypeManager->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME);
      /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $published_version */
      $published_version = $storage->load($form_state->getValue('published_version'));
      $this->entity->setPublishedVersion($published_version);
    }

    return $this;
  }

  /**
   * Methods for presenting the legal document to end users.
   *
   * @param string $type
   *   The type of user, 'new_users' or 'existing_users'.
   *
   * @return array
   *   Methods available keyed by method name and title.
   */
  protected function getAcceptanceDeliveryMethodOptions($type) {
    $methods = [];

    foreach ($this->pluginManager->getDefinitions() as $plugin) {
      if ($plugin['type'] == $type) {
        $methods[$plugin['id']] = $plugin['label'];
      }
    }

    return $methods;
  }

}
