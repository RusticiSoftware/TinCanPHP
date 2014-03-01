<?php

date_default_timezone_set('UTC');

require_once('tests/Constants.php');

require_once('src/LRSInterface.php');
require_once('src/StatementTargetInterface.php');
require_once('src/VersionableInterface.php');

require_once('src/FromJSONTrait.php');

require_once('src/Util.php');
require_once('src/Version.php');
require_once('src/Activity.php');
require_once('src/Agent.php');
require_once('src/Verb.php');
require_once('src/Statement.php');
require_once('src/RemoteLRS.php');

register_shutdown_function(
    function () {
        if ($err = error_get_last()) {
            print "\n\nNon-exception error occurred:\n\n";
            print $err['type'] . ": " . $err['message'] . "\n";
            print $err['file'] . " (" . $err['line'] . ")\n\n";
        }
    }
);
