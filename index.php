<?php


use Libs\Nest\Nest;

require_once('autoloader.php');

$nest_settings = Common::settings('nest');
$nest = new Nest($nest_settings['username'], $nest_settings['password']);
$locations = $nest->getUserLocations();
$devices = $nest->getDevices();
$device_info = array();
foreach ($devices as $thermostat) {
  $device_info[$thermostat] = $nest->getDeviceInfo($thermostat);
  printf("%.02f degrees %s\n", $device_info[$thermostat]->current_state->temperature, $device_info[$thermostat]->scale);
}
$db = Common::db();
$status = $db->get('status');