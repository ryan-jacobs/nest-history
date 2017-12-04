<?php
/**
 * Common utilities and tools
 */

use Libs\Spyc\Spyc;
use Libs\MysqliDb\MysqliDb;


class Common {
  
  /**
   * Loaded settings used for static caching purposes.
   *
   * @var array
   */
  static private $settings = array();
  
  /**
   * Instatntiated db object used for static caching purposes.
   *
   * @var \Libs\MysqliDb\MysqliDb
   */
  static private $db;

  /**
   * Get global settings.
   *
   * @param string $group
   *   Optional. Define a specific group of settings.
   * @return array
   *   If group is defined, the group-speific settings are returned, otherwise
   *   all settings are returned.
   */
  public static function settings($group = NULL) {
    // Only parse settings from yaml once per request.
    if (!static::$settings) {
      static::$settings = Spyc::YAMLLoad(realpath('conf/settings.yml'));
    }
    if ($group) {
      return isset(static::$settings[$group]) ? static::$settings[$group] : NULL;
    }
    return static::$settings;
  }

  /**
   * Get database object.
   *
   * @return \Libs\MysqliDb\MysqliDb
   *   The master database object initiated with global connection settings.
   */
  public static function db() {
    // Only initiate the db once per request.
    if (!static::$db) {
      $db_settings = self::settings('db') + array(
        'port' => 3306,
        'prefix' => '',
        'charset' => 'utf8');
      static::$db = new MySqliDb($db_settings);
    }
    return static::$db;
 }

}
