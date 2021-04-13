<?php

namespace Drupal\entity_legal\Plugin\EntityLegal;

use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\entity_legal\EntityLegalDocumentInterface;
use Drupal\entity_legal\EntityLegalPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Method class for redirecting existing users to accept a legal document.
 *
 * @EntityLegal(
 *   id = "redirect",
 *   label = @Translation("Redirect every page load to legal document until accepted"),
 *   type = "existing_users",
 * )
 */
class Redirect extends EntityLegalPluginBase implements ContainerFactoryPluginInterface {

  use MessengerTrait;
  use RedirectDestinationTrait;
  use StringTranslationTrait;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The private temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * Constructs a new plugin instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
   *   The current route match service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store_factory
   *   The private temp store factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ResettableStackedRouteMatchInterface $route_match, AccountProxyInterface $current_user, PrivateTempStoreFactory $private_temp_store_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->currentUser = $current_user;
    $this->tempStore = $private_temp_store_factory->get('entity_legal');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('current_user'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array &$context = []) {
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    foreach ($this->documents as $document) {
      /** @var \Symfony\Component\HttpKernel\Event\GetResponseEvent $event */
      $event = $context['event'];
      $request = $event->getRequest();

      // The acceptance of a legal document is applicable only to humans.
      if ($request->getRequestFormat() !== 'html') {
        return FALSE;
      }

      // Don't redirect on POST requests.
      if (!$request->isMethodSafe()) {
        return FALSE;
      }

      if (!$route_name = $this->routeMatch->getRouteName()) {
        // Unrouted?
        return FALSE;
      }

      if ($this->isExcludedRoute($route_name, $document)) {
        return FALSE;
      }

      // Do not redirect password reset.
      if ($this->isPasswordReset($event->getRequest())) {
        return FALSE;
      }

      if ($messages = $this->messenger()->all()) {
        // Save any messages set for the destination page.
        // @see \Drupal\entity_legal\Form\EntityLegalDocumentAcceptanceForm::submitForm()
        $this->tempStore->set('postponed_messages', $messages);
        $this->messenger()->deleteAll();
      }

      $this->messenger()->addWarning($this->t('You must accept this agreement before continuing.'));

      $entity_url = $document->toUrl()
        ->setOption('query', $this->getDestinationArray())
        ->setAbsolute(TRUE)
        ->toString();
      $event->setResponse(new TrustedRedirectResponse($entity_url));

      // Remove destination cause the RedirectResponseSubscriber redirects and
      // in some cases it brings redirect loops.
      $request->query->remove('destination');
      $request->request->remove('destination');
    }
  }

  /**
   * Checks if the current route is excluded.
   *
   * @param string $route_name
   *   The route name.
   * @param \Drupal\entity_legal\EntityLegalDocumentInterface $document
   *   The legal document entity.
   *
   * @return bool
   *   If the current route is excluded.
   */
  protected function isExcludedRoute($route_name, EntityLegalDocumentInterface $document) {
    $excluded_routes = [
      'system.csrftoken',
      'user.logout',
      $document->toUrl()->getRouteName(),
    ];
    return in_array($route_name, $excluded_routes);
  }

  /**
   * Check if this is a valid password reset request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return bool
   *   If this is a valid password reset request.
   */
  protected function isPasswordReset(Request $request) {
    // Unblock only the current user account edit form.
    if ($this->routeMatch->getRouteName() !== 'entity.user.edit_form' && $this->routeMatch->getRawParameter('user') != $this->currentUser->id()) {
      return FALSE;
    }

    // The password reset token should be present.
    if (!$pass_reset_token = $request->get('pass-reset-token')) {
      return FALSE;
    }

    // Now we check if it's a valid token.
    // @see \Drupal\user\Controller\UserController::resetPassLogin()
    // @see \Drupal\user\AccountForm::form()
    $session_key = "pass_reset_{$this->currentUser->id()}";
    if (!isset($_SESSION[$session_key]) || !hash_equals($_SESSION[$session_key], $pass_reset_token)) {
      return FALSE;
    }

    return TRUE;
  }

}
