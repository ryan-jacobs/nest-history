<?php

require_once('autoloader.php');

$poller = Common::poller();
$poller->pollConf();
print 'done!';
exit();