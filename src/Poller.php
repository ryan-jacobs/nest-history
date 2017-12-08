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
   * Constructor
   */
  public function __construct(\MysqliDb $db, \Nest $nest) {
    $this->db = $db;
    $this->nest = $nest;
  }

  /**
   * Poll base structure and device data.
   */
  public function pollConf() {
    $structures = $this->nest->getUserLocations();
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
    $devices = $this->nest->getDevices(DEVICE_TYPE_THERMOSTAT);
    foreach ($devices as $thermostat) {
      $device_info = $this->nest->getDeviceInfo($thermostat);
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

}
