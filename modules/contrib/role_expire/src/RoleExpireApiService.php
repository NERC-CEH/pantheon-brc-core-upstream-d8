<?php

namespace Drupal\role_expire;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Session\AccountInterface;

/**
 * Class RoleExpireApiService.
 */
class RoleExpireApiService {

  /**
   * Configuration factory.
   *
   * Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Database connection.
   *
   * Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Session manager.
   *
   * Drupal\Core\Session\SessionManager
   */
  protected $sessionManager;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Constructs a new RoleExpireApiService object.
   */
  public function __construct(ConfigFactory $configFactory, Connection $connection, ModuleHandler $moduleHandler) {
    $this->config = $configFactory;
    $this->database = $connection;
    $this->sessionManager = \Drupal::service('session_manager');
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Get expiration time of a user role.
   *
   * @param int $uid
   *   User ID.
   * @param string $rid
   *   Role ID.
   *
   * @return array
   *   Array with the expiration time.
   */
  public function getUserRoleExpiryTime($uid, $rid) {

    $query = $this->database->select('role_expire', 'n');
    $query->fields('n', ['expiry_timestamp']);
    $query->condition('n.uid', $uid, '=');
    $query->condition('n.rid', $rid, '=');
    $result = $query->execute()->fetchField();

    return (!empty($result)) ? $result : '';
  }

  /**
   * Get expiration of all roles of a user.
   *
   * @param int $uid
   *   User ID.
   *
   * @return array
   *   Array with the expiration time.
   */
  public function getAllUserRecords($uid) {

    $query = $this->database->select('role_expire', 'n');
    $query->fields('n', [
      'rid',
      'expiry_timestamp',
    ]
    );
    $query->condition('n.uid', $uid, '=');
    $result = $query->execute()->fetchAll();

    $return = [];
    foreach ($result as $row) {
      $return[$row->rid] = $row->expiry_timestamp;
    }

    return $return;
  }

  /**
   * Delete a record from the database.
   *
   * @param int $uid
   *   User ID.
   * @param string $rid
   *   Role ID.
   * @param bool $delete_session
   *   Whether to terminate user session or not.
   */
  public function deleteRecord($uid, $rid, $delete_session = TRUE) {
    $query = $this->database->delete('role_expire');
    $query->condition('uid', $uid)->condition('rid', $rid);
    $query->execute();

    if ($delete_session) {
      // Delete the user's sessions so they have login again with their new access.
      $this->sessionManager->delete($uid);
    }
  }

  /**
   * Delete all records for role.
   *
   * @param string $rid
   *   Role ID.
   */
  public function deleteRoleRecords($rid) {
    $this->database->delete('role_expire')->condition('rid', $rid)->execute();
  }

  /**
   * Delete all user expirations.
   *
   * @param int $uid
   *   User ID.
   * @param bool $delete_session
   *   Whether to terminate user session or not.
   */
  public function deleteUserRecords($uid, $delete_session = TRUE) {
    $this->database->delete('role_expire')->condition('uid', $uid)->execute();

    if ($delete_session) {
      // Delete the user's sessions so they have login again with their new access.
      $this->sessionManager->delete($uid);
    }
  }

  /**
   * Insert or update a record in the database.
   *
   * @param int $uid
   *   User ID.
   * @param string $rid
   *   Role ID.
   * @param int $expiry_timestamp
   *   The expiration timestamp.
   */
  public function writeRecord($uid, $rid, $expiry_timestamp) {

    // Delete previous expiry for user and role if it exists.
    $this->deleteRecord($uid, $rid);

    // Insert new expiry for user and role.
    $query = $this->database->insert('role_expire');
    $query->fields(['uid', 'rid', 'expiry_timestamp']);
    $query->values(['uid' => $uid, 'rid' => $rid, 'expiry_timestamp' => $expiry_timestamp]);
    $query->execute();
  }

  /**
   * Get the default duration for a role.
   *
   * @param string $rid
   *   Required. The role_id to check.
   *
   * @return string
   *   String containing the strtotime compatible default duration of the role
   *   or empty string if not set.
   */
  public function getDefaultDuration($rid) {

    $values_raw = $this->config->get('role_expire.config')->get('role_expire_default_duration_roles');
    $values = empty($values_raw) ? [] : $values_raw;
    $result = isset($values[$rid]) ? $values[$rid] : '';
    return (!empty($result)) ? $result : '';
  }

  /**
   * Insert or update the default expiry duration for a role.
   *
   * @param string $rid
   *   Role ID.
   * @param string $duration
   *   The strtotime-compatible duration string.
   */
  public function setDefaultDuration($rid, $duration) {

    if (!empty($duration)) {
      // Insert new default duration.
      $config = $this->config->getEditable('role_expire.config');
      $values_raw = $config->get('role_expire_default_duration_roles');
      $values = empty($values_raw) ? [] : $values_raw;
      $values[$rid] = Html::escape($duration);
      $config->set('role_expire_default_duration_roles', $values)->save();
    }
  }

  /**
   * Delete default duration(s) for a role.
   *
   * @param string $rid
   *   Required. The role_id to remove.
   */
  public function deleteDefaultDuration($rid) {
    $config = $this->config->getEditable('role_expire.config');
    $values_raw = $config->get('role_expire_default_duration_roles');
    $values = empty($values_raw) ? [] : $values_raw;
    if (isset($values[$rid])) {
      unset($values[$rid]);
    }
    $config->set('role_expire_default_duration_roles', $values)->save();
  }

  /**
   * Get all records that should be expired.
   *
   * @param int $time
   *   Optional. The time to check, if not set it will check current time.
   *
   * @return array
   *   All expired roles.
   */
  public function getExpired($time = '') {
    $return = [];
    if (!$time) {
      date_default_timezone_set(date_default_timezone_get());
      $time = \Drupal::time()->getRequestTime();
    }

    $query = $this->database->select('role_expire', 'n');
    $query->fields('n', [
      'rid',
      'uid',
      'expiry_timestamp',
    ]
    );
    $query->condition('n.expiry_timestamp', $time, '<=');
    $result = $query->execute()->fetchAll();

    foreach ($result as $row) {
      $return[] = $row;
    }
    return $return;
  }

  /**
   * Get roles to assign on expiration (global configuration).
   *
   * @return array
   *   Returns an array where the key is the original rid and the value the
   *   one that has to be assigned on expiration. The array will be empty if
   *   configuration is not set.
   */
  public function getRolesAfterExpiration() {
    $values_raw = $this->config->get('role_expire.config')->get('role_expire_default_roles');
    $values = empty($values_raw) ? [] : json_decode($values_raw, TRUE);
    return $values;
  }

  /**
   * Get role expiration status for each role.
   *
   * @return array
   *   Returns an array where the key is the original rid and the value
   *   is 0 if role should have expiration and 1 if it shouldn't.
   */
  public function getRolesExpirationStatus() {
    $values_raw = $this->config->get('role_expire.config')->get('role_expire_disabled_roles');
    $values = empty($values_raw) ? [] : json_decode($values_raw, TRUE);
    return $values;
  }

  /**
   * Get rid of all enabled roles.
   *
   * @return array
   *   Returns an array where the values are the enabled roles.
   */
  public function getEnabledExpirationRoles() {
    $out = [];
    $roleExpirationStatus = $this->getRolesExpirationStatus();
    foreach ($roleExpirationStatus as $rid => $disabled) {
      if ($disabled == 0) {
        $out[] = $rid;
      }
    }

    if (empty($out)) {
      /*
       * If the module is just installed, configuration could be empty.
       * We should return all roles to have role expiration.
       */
      $roles = user_roles(TRUE);
      unset($roles[AccountInterface::AUTHENTICATED_ROLE]);
      foreach ($roles as $role) {
        $out[] = $role->id();
      }
    }

    return $out;
  }

  /**
   * Sets the default role duration for the current user/role combination.
   *
   * It won't override the current expiration time for user's role.
   *
   * @param string $role_id
   *   The ID of the role.
   * @param int $uid
   *   The user ID.
   */
  function processDefaultRoleDurationForUser($role_id, $uid) {
    // Does a default expiry exist for this role?
    $default_duration = $this->getDefaultDuration($role_id);
    if ($default_duration) {
      $user_role_expiry = $this->getUserRoleExpiryTime($uid, $role_id);
      // If the expiry is empty then we act!.
      if (!$user_role_expiry) {
        // Use strtotime of default duration.
        $this->writeRecord($uid, $role_id, strtotime($default_duration));
        \Drupal::logger('role_expire')->notice(t('Added default duration @default_duration to role @role to user @account.', array('@default_duration' => $default_duration, '@role' => $role_id, '@account' => $uid)));
      }
    }
  }

  /**
   * On user form save we decide whether to delete role expiration or not.
   *
   * @param string $rid
   *   Role ID.
   *
   * @return bool
   *   Return TRUE if expiration for role can be deleted.
   */
  function roleExpirationCanBeDeletedOnUserEditSave($rid) {
    $currentUser = \Drupal::currentUser();
    if ($currentUser->hasPermission('administer permissions')) {
      /*
       * User with this permission won't be limited by roleassign and
       * role_delegation modules.
       */
      return TRUE;
    }

    if ($this->moduleHandler->moduleExists('roleassign')) {
      if ($currentUser->hasPermission('assign roles')) {
        $roleassign_config = $this->config->get('roleassign.settings')
          ->get('roleassign_roles');
        $assignable_roles = array_values($roleassign_config);
        if (!in_array($rid, $assignable_roles)) {
          /*
           * Current user doesn't have permission to assign this role. So,
           * we shouldn't delete it's expiration time only because he/she
           * doesn't see it.
           */
          return FALSE;
        }
      }
    }

    if ($this->moduleHandler->moduleExists('role_delegation')) {
      if (!$currentUser->hasPermission(sprintf('assign %s role', $rid))) {
        /*
         * Current user doesn't have permission to assign this role. So,
         * we shouldn't delete it's expiration time only because he/she
         * doesn't see it.
         */
        return FALSE;
      }
    }

    return TRUE;
  }

}
