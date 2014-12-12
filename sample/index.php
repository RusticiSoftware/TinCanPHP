<?php

require_once( '../TinCanApi_Autoloader.php' );

$lrs = new TinCanAPI_RemoteLRS(
    'http://cloud.scorm.com/tc/public',
    '1.0.1',
    'user',
    'pass'
);
$response = $lrs->queryStatements(['limit' => 2]);
print_r($response);

