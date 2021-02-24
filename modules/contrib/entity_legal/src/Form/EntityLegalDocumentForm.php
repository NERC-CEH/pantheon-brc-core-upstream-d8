<?php

namespace Drupal\entity_legal\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\ConfigFormBaseTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Base form for contact form edit forms.
 */
class EntityLegalDocumentForm extends EntityForm implements ContainerInjectionInterface {

  use ConfigFormBaseTrait;

  /**
   * The path alias storage.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
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
  public function __construct(AliasStorageInterface $alias_storage, PluginManagerInterface $plugin_manager, AccountProxy $currentUser, ModuleHandler $moduleHandler) {
    $this->aliasStorage = $alias_storage;
    $this->pluginManager = $plugin_manager;
    $this->currentUser = $currentUser;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.alias_storage'),
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
      '#title'         => $this->t('Administrative label'),
      '#type'          => 'textfield',
      '#default_value' => $this->entity->label(),
      '#required'      => TRUE,
    ];

    $form['id'] = [
      '#type'          => 'machine_name',
      '#title'         => t('Machine-readable name'),
      '#required'      => TRUE,
      '#default_value' => $this->entity->id(),
      '#machine_name'  => [
        'exists' => '\Drupal\entity_legal\Entity\EntityLegalDocument::load',
      ],
      '#disabled'      => !$this->entity->isNew(),
      '#maxlength'     => EntityTypeInterface::BUNDLE_MAX_LENGTH,
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
        'title'      => t('Title'),
        'created'    => t('Created'),
        'changed'    => t('Updated'),
        'operations' => t('Operations'),
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
        $options[$version->id()] = [
          'title'      => Link::createFromRoute($version->label(), $route_name, $route_parameters),
          'created'    => $version->getFormattedDate('created'),
          'changed'    => $version->getFormattedDate('changed'),
          'operations' => Link::createFromRoute(t('Edit'), 'entity.entity_legal_document_version.edit_form', [
            'entity_legal_document'         => $this->entity->id(),
            'entity_legal_document_version' => $version->id(),
          ]),
        ];
      }

      // By default just show a simple overview for all entities.
      $form['versions'] = [
        '#type'        => 'details',
        '#title'       => t('Current version'),
        '#description' => t('The current version users must agree to. If requiring existing users to accept, those users will be prompted if they have not accepted this particular version in the past.'),
        '#open'        => TRUE,
        '#tree'        => FALSE,
      ];

      $form_state->set('published_version', $published_version);
      $form['versions']['published_version'] = [
        '#type'          => 'tableselect',
        '#header'        => $header,
        '#options'       => $options,
        '#empty'         => t('Create a document version to set up a default'),
        '#multiple'      => FALSE,
        '#default_value' => $published_version,
      ];
    }

    $form['settings'] = [
      '#type'   => 'vertical_tabs',
      '#weight' => 27,
    ];

    $form['new_users'] = [
      '#title'       => t('New users'),
      '#description' => t('Visit the <a href=":permissions">permissions</a> page to ensure that users can view the document.', [
        ':permissions' => Url::fromRoute('user.admin_permissions')->toString(),
      ]),
      '#type'        => 'details',
      '#group'       => 'settings',
      '#parents'     => ['settings', 'new_users'],
      '#tree'        => TRUE,
    ];

    $form['new_users']['require'] = [
      '#title'         => t('Require new users to accept this agreement on signup'),
      '#type'          => 'checkbox',
      '#default_value' => $this->entity->get('require_signup'),
    ];

    $form['new_users']['require_method'] = [
      '#title'         => t('Present to user as'),
      '#type'          => 'select',
      '#options'       => $this->getAcceptanceDeliveryMethodOptions('new_users'),
      '#default_value' => $this->entity->getAcceptanceDeliveryMethod(TRUE),
      '#states'        => [
        'visible' => [
          ':input[name="settings[new_users][require]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['existing_users'] = [
      '#title'       => t('Existing users'),
      '#description' => t('Visit the <a href=":permissions">permissions</a> page to configure which existing users these settings apply to.', [
        ':permissions' => Url::fromRoute('user.admin_permissions')->toString(),
      ]),
      '#type'        => 'details',
      '#group'       => 'settings',
      '#parents'     => ['settings', 'existing_users'],
      '#tree'        => TRUE,
    ];

    $form['existing_users']['require'] = [
      '#title'         => t('Require existing users to accept this agreement'),
      '#type'          => 'checkbox',
      '#default_value' => $this->entity->get('require_existing'),
    ];

    $form['existing_users']['require_method'] = [
      '#title'         => t('Present to user as'),
      '#type'          => 'select',
      '#options'       => $this->getAcceptanceDeliveryMethodOptions('existing_users'),
      '#default_value' => $this->entity->getAcceptanceDeliveryMethod(),
      '#states'        => [
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

    $path = [];
    if (!$this->entity->isNew()) {
      $conditions = ['source' => '/' . $this->entity->toUrl()->getInternalPath()];
      $path = $this->aliasStorage->load($conditions);
      if ($path === FALSE) {
        $path = [];
      }
    }
    $path += [
      'pid'    => NULL,
      'source' => !$this->entity->isNew() ? '/' . $this->entity->toUrl()->getInternalPath() : NULL,
      'alias'  => '',
    ];

    $form['path'] = [
      '#type'             => 'details',
      '#title'            => t('URL path settings'),
      '#group'            => 'settings',
      '#attributes'       => [
        'class' => ['path-form'],
      ],
      '#attached'         => [
        'library' => ['path/drupal.path'],
      ],
      '#access'           => $this->currentUser->hasPermission('create url aliases') || $this->currentUser->hasPermission('administer url aliases'),
      '#weight'           => 5,
      '#tree'             => TRUE,
      '#element_validate' => [['\Drupal\path\Plugin\Field\FieldWidget\PathWidget', 'validateFormElement']],
      '#parents'          => ['path', 0],
    ];

    $form['path']['langcode'] = [
      '#type' => 'language_select',
      '#title' => t('Language'),
      '#languages' => LanguageInterface::STATE_ALL,
      '#default_value' => $this->entity->language()->getId(),
    ];

    $form['path']['alias'] = [
      '#type'          => 'textfield',
      '#title'         => t('URL alias'),
      '#default_value' => $path['alias'],
      '#maxlength'     => 255,
      '#description'   => $this->t('The alternative URL for this content. Use a relative path. For example, enter "/about" for the about page.'),
    ];

    $form['path']['pid'] = [
      '#type'  => 'value',
      '#value' => $path['pid'],
    ];

    $form['path']['source'] = [
      '#type'  => 'value',
      '#value' => $path['source'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->set('require_signup', $this->entity->get('settings')['new_users']['require']);
    $this->entity->set('require_existing', $this->entity->get('settings')['existing_users']['require']);

    $status = $this->entity->save();
    if ($status == SAVED_NEW) {
      $form_state->setRedirect('entity.entity_legal_document_version.add_form', ['entity_legal_document' => $this->entity->id()]);
    }

    if (!empty($form_state->getValues()['path'][0]) && (!empty($form_state->getValues()['path'][0]['alias']) || !empty($form_state->getValues()['path'][0]['pid']))) {
      $path = $form_state->getValues()['path'][0];

      $path['alias'] = trim($path['alias']);
      if (!$path['source']) {
        $path['source'] = $this->entity->toUrl()->toString();
      }

      // Delete old alias if user erased it.
      if (!empty($path['pid']) && empty($path['alias'])) {
        $this->aliasStorage->delete(['pid' => $path['pid']]);
      }

      else {
        $this->aliasStorage->save($path['source'], $path['alias'], LanguageInterface::LANGCODE_NOT_SPECIFIED, $path['pid']);
      }
    }

    // Update the published version.
    if ($form_state->get('published_version') && $form_state->get('published_version') !== $form_state->getValue('published_version')) {
      $storage = $this->entityTypeManager->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME);
      /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $published_version */
      $published_version = $storage->load($form_state->getValue('published_version'));
      $this->entity->setPublishedVersion($published_version);
    }
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
