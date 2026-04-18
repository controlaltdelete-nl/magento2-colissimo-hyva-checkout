<?php

$config = require('app/etc/config.php');
$merge = require('merge-config.php.stub');

$merged = $config + $merge;

file_put_contents('app/etc/config.php', '<?php return ' . var_export($merged, true) . ';');