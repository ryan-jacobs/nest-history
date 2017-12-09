<?php
/**
 * Poller utilities and tools
 */

namespace rjacobs\NestHistory;

class Poller {

  /**
   * Database object.
   *
   * @var \MysqliDb
   */
  private $db;

  /**
   * Nest object.
   *
   * @var \Nest
   */
  private $nest;

  /**
   * Structure data for caching purposes.
   *
   * @var array
   */
  private $structures;

  /**
   * Device data for caching purposes.
   *
   * @var array
   */
  private $thermostats;

  /**
   * Constructor
   */
  public function __construct(\MysqliDb $db, \Nest $nest) {
    $this->db = $db;
    $this->nest = $nest;
  }

  /**
   * Poll base structure and device conf.
   */
  public function pollConf() {
    $structures = $this->getStructures();
    $structures_by_uuid = array();
    // Update structure data.
    foreach ($structures as $structure) {
      $structure_data = array(
        'uuid' => $structure->id,
        'country' => $structure->country,
        'postal_code' => $structure->postal_code,
        'city' => $structure->city,
        'name' => $structure->name,
        'last_polled' => $this->db->now(),
      );
      $this->db->onDuplicate(array('country', 'postal_code', 'city', 'name', 'last_polled'));
      $id = $this->db->insert('structures', $structure_data);
      $structures_by_uuid[$structure->id] = $id;
    }
    // Update thermostat data.
    $devices = $this->getThermostats();
    foreach ($devices as $device_info) {
      $thermostat_data = array(
        'serial' => $device_info->serial_number,
        'structure_id' => $structures_by_uuid[$device_info->location],
        'where' => $device_info->where,
        'name' => $device_info->name,
        'scale' => $device_info->scale,
        'last_polled' => $this->db->now(),
      );
      $this->db->onDuplicate(array('where', 'name', 'scale', 'last_polled'));
      $id = $this->db->insert('thermostats', $thermostat_data);
    }
  }

  /**
   * Poll current structure status.
   */
  public function pollStructures() {
    foreach ($this->getStructures() as $structure) {
      // Get internal id for this structure.
      $structure_db = $this->db->where('uuid', $structure->id)->getOne('structures');
      if ($structure_db) {
        $data = array(
          'structure_id' => $structure_db['id'],
          'temperature' => $this->temp($structure->outside_temperature),
          'away' => $structure->away,
          'polled' => $this->db->now(),
        );
        foreach (array('conditions', 'conditions_icon', 'humidity', 'wind', 'wind_speed') as $field) {
          $data[$field] = $structure->{'outside_' . $field};
        }
        $id = $this->db->insert('structure_status', $data);
      }
    }
  }

  /**
   * Poll current device status.
   */
  public function pollThermostats() {
    foreach ($this->getThermostats() as $thermostat) {
      $state = $thermostat->current_state;
      // Get internal id for this device.
      $thermostat_db = $this->db->where('serial', $thermostat->serial_number)->getOne('thermostats');
      if ($thermostat_db) {
        $data = array(
          'thermostat_id' => $thermostat_db['id'],
          'temperature' => $this->temp($state->temperature, $thermostat->serial_number),
          'eco_temp_high' => $this->temp($state->eco_temperatures->high, $thermostat->serial_number),
          'eco_temp_low' => $this->temp($state->eco_temperatures->low, $thermostat->serial_number),
          'target_mode' => $thermostat->target->mode,
          'target_temperature' => $this->temp($thermostat->target->temperature, $thermostat->serial_number),
          'target_time' => $thermostat->target->time_to_target,
          'polled' => $this->db->now()
        );
        foreach (array('heat', 'alt_heat', 'ac', 'fan', 'auto_away', 'manual_away', 'humidity', 'leaf', 'mode', 'battery_level') as $field) {
          $data[$field] = $state->{$field};
        }
        $id = $this->db->insert('thermostat_status', $data);
      }
    }
  }

  /**
   * Utility to get and cache structure data including weather.
   *
   * The Nest API may perform some redundant remote REST requests upon each
   * getUserLocations() call, such as weather lookups, so this method wraps that
   * call with some caching.
   *
   * @return array
   *   Indexed array of structure data.
   */
  private function getStructures() {
    if (!$this->structures) {
      $this->structures = $this->nest->getUserLocations();
    }
    return $this->structures;
  }

  /**
   * Utility to get device data for all thermostats.
   *
   * @return array
   *   Array keyed by thermostat serial number with full device data for each
   *   thermostat.
   */
  private function getThermostats() {
    // @todo, consider if we need this caching. It's possible that this is
    // redundant to the API's own cache. Unlike a getUserLocations() request,
    // these calls do not seem to create redundant REST requests.
    if (!$this->thermostats) {
      $devices = $this->nest->getDevices(DEVICE_TYPE_THERMOSTAT);
      foreach ($devices as $thermostat) {
        $this->thermostats[$thermostat] = $this->nest->getDeviceInfo($thermostat);
      }
    }
    return $this->thermostats;
  }

  /**
   * Utility to get celsius temperature independent of the user's scale.
   *
   * When fetching tempatures the Nest API always returns values in the user's
   * scale. However, we want to store everything in a standard celsius and then
   * convert at dispaly time.
   *
   * @param float $temp
   *   The temperature specified in the user's scale.
   * @param string $serial_number
   *   The serial number of the thermostat that this temperature value came
   *   from. This is needed to determine the user's scale. If NULL the scale of
   *   the first thermostat will be used.
   * @return float
   *   The temperature in celsius.
   */
  private function temp($temp, $serial_number = NULL) {
    $temp_scale = $this->nest->getDeviceTemperatureScale($serial_number);
    if ($temp_scale == 'F') {
      $temp = 5/9 * ($temp - 32);
    }
    return $temp;
  }

}
