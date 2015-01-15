<?php

date_default_timezone_set('UTC');

require_once('autoload.php');
require_once('tests/Config.php');
require_once('tests/Constants.php');

register_shutdown_function(
    function () {
        if ($err = error_get_last()) {
            print "\n\nNon-exception error occurred:\n\n";
            print $err['type'] . ": " . $err['message'] . "\n";
            print $err['file'] . " (" . $err['line'] . ")\n\n";
        }
    }
);
