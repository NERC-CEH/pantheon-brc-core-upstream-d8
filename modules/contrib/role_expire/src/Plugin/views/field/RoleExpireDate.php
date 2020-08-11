<?php

namespace Drupal\role_expire\Plugin\views\field;

use Drupal\views\ViewExecutable;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\PrerenderList;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\role_expire\RoleExpireApiService;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display the role expire date.
 *
 * @ingroup views_field_handlers
 *
 * @see Drupal\views\Plugin\views\field\Date
 * @see Drupal\user\Plugin\views\field\Roles
 *
 * @ViewsField("role_expire_date")
 */
class RoleExpireDate extends PrerenderList {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateFormatStorage;

  /**
   * Role expire API service.
   *
   * @var \Drupal\role_expire\RoleExpireApiService
   */
  protected $roleExpireApi;

  /**
   * Constructs a new Date object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $date_format_storage
   *   The date format storage.
   * @param \Drupal\role_expire\RoleExpireApiService $role_expire_api
   *   Role expire API service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter, EntityStorageInterface $date_format_storage, RoleExpireApiService $role_expire_api) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->dateFormatter = $date_formatter;
    $this->dateFormatStorage = $date_format_storage;
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
      $container->get('entity_type.manager')->getStorage('date_format'),
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
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['date_format'] = ['default' => 'small'];
    $options['custom_date_format'] = ['default' => 'Y-m-d H:i'];
    $options['timezone'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $currentTimestamp = \Drupal::time()->getRequestTime();

    $date_formats = [];
    foreach ($this->dateFormatStorage->loadMultiple() as $machine_name => $value) {
      $date_formats[$machine_name] = $this->t('@name format: @date', ['@name' => $value->label(), '@date' => $this->dateFormatter->format($currentTimestamp, $machine_name)]);
    }

    $form['date_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Date format'),
      '#options' => $date_formats + [
        'custom' => $this->t('Custom'),
      ],
      '#default_value' => isset($this->options['date_format']) ? $this->options['date_format'] : 'small',
    ];
    $form['custom_date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom date format'),
      '#description' => $this->t('If "Custom", see <a href="http://us.php.net/manual/en/function.date.php" target="_blank">the PHP docs</a> for date formats. Otherwise, enter the number of different time units to display, which defaults to 2.'),
      '#default_value' => isset($this->options['custom_date_format']) ? $this->options['custom_date_format'] : '',
    ];
    // Setup #states for all possible date_formats on the custom_date_format form element.
    foreach (['custom', 'raw time ago', 'time ago', 'raw time hence', 'time hence', 'raw time span', 'time span', 'raw time span', 'inverse time span', 'time span'] as $custom_date_possible) {
      $form['custom_date_format']['#states']['visible'][] = [
        ':input[name="options[date_format]"]' => ['value' => $custom_date_possible],
      ];
    }
    $form['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Timezone'),
      '#description' => $this->t('Timezone to be used for date output.'),
      '#options' => ['' => $this->t('- Default site/user timezone -')] + system_time_zones(FALSE, TRUE),
      '#default_value' => $this->options['timezone'],
    ];
    foreach (array_merge(['custom'], array_keys($date_formats)) as $timezone_date_formats) {
      $form['timezone']['#states']['visible'][] = [
        ':input[name="options[date_format]"]' => ['value' => $timezone_date_formats],
      ];
    }

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function preRender(&$values) {
    $this->items = [];

    if (is_array($values)) {

      $format = $this->options['date_format'];
      if (in_array($format, ['custom'])) {
        $custom_format = $this->options['custom_date_format'];
      }
      else {
        $custom_format = '';
      }

      $timezone = !empty($this->options['timezone']) ? $this->options['timezone'] : NULL;

      foreach ($values as $user) {
        $expirations = $this->roleExpireApi->getAllUserRecords($user->uid);
        foreach ($expirations as $role => $timestamp) {
          $date = $this->dateFormatter->format($timestamp, $format, $custom_format, $timezone);
          $this->items[$user->uid][$role]['date'] = $date;
        }
      }
    }
  }

  /**
   * @{inheritdoc}
   */
  public function render_item($count, $item) {
    return $item['date'];
  }

}
