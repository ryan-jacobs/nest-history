<?php

use rjacobs\NestHistory\Common;

require_once('autoloader.php');

$poller = Common::poller();
$poller->pollConf();
$poller->pollStructures();
exit();