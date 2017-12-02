<?php

use Libs\Nest\Nest;
use Libs\Spyc\Spyc;

require_once('autoloader.php');

$settings = Spyc::YAMLLoad(realpath('conf/settings.yml'));
$nest = new Nest($settings['nest_user'], $settings['nest_pass']);
$locations = $nest->getUserLocations();
$devices = $nest->getDevices();
$device_info = array();
foreach ($devices as $thermostat) {
  $device_info[$thermostat] = $nest->getDeviceInfo($thermostat);
  printf("%.02f degrees %s\n", $device_info[$thermostat]->current_state->temperature, $device_info[$thermostat]->scale);
}