<?php

namespace Drupal\role_expire\Plugin\views\field;

use Drupal\views\ViewExecutable;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\PrerenderList;
use Drupal\role_expire\RoleExpireApiService;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display the role expire data.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("role_expire_expiry_data")
 */
class RoleExpireExpiryData extends PrerenderList {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Role expire API service.
   *
   * @var \Drupal\role_expire\RoleExpireApiService
   */
  protected $roleExpireApi;

  /**
   * Constructs a \Drupal\user\Plugin\views\field\Roles object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\role_expire\RoleExpireApiService $role_expire_api
   *   Role expire API service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter, RoleExpireApiService $role_expire_api) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->dateFormatter = $date_formatter;
    $this->roleExpireApi = $role_expire_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter'),
      $container->get('role_expire.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['uid'] = ['table' => 'users_field_data', 'field' => 'uid'];
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    $this->addAdditionalFields();
    $this->field_alias = $this->aliases['uid'];
  }

  /**
   * Define the available options.
   *
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['custom_date_format'] = ['default' => 'Y-m-d H:i'];
    $options['timezone'] = ['default' => ''];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['custom_date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom date format'),
      '#description' => $this->t('See <a href="http://us.php.net/manual/en/function.date.php" target="_blank">the PHP docs</a> for date formats.'),
      '#default_value' => isset($this->options['custom_date_format']) ? $this->options['custom_date_format'] : '',
    ];

    $form['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Timezone'),
      '#description' => $this->t('Timezone to be used for date output.'),
      '#options' => ['' => $this->t('- Default site/user timezone -')] + system_time_zones(FALSE),
      '#default_value' => $this->options['timezone'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function preRender(&$values) {
    $this->items = [];

    if (is_array($values)) {

      $format = $this->options['custom_date_format'];
      $timezone = !empty($this->options['timezone']) ? $this->options['timezone'] : NULL;

      foreach ($values as $user) {
        $expirations = $this->roleExpireApi->getAllUserRecords($user->uid);
        foreach ($expirations as $role => $timestamp) {
          $date = $this->dateFormatter->format($timestamp, 'custom', $format, $timezone);
          $this->items[$user->uid][$role]['expireData'] = $this->t('@role (@date)', ['@role' => $role, '@date' => $date]);
        }
      }
    }
  }

  /**
   * @{inheritdoc}
   */
  public function render_item($count, $item) {
    return $item['expireData'];
  }

}
