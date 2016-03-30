<?php
/*
    Copyright 2015 Rustici Software

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
*/

date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 'stdout');

require_once('autoload.php');
require_once(__DIR__ . '/config.php');

if (!trait_exists('TinCanTest\\TestCompareWithSignatureTrait')) {
    tincan_register_autoloader('TinCanTest\\', 'tests');
}

register_shutdown_function(function() {
    if ($err = error_get_last()) {
        print "\n\nNon-exception error occurred:\n\n";
        print $err['type'] . ": " . $err['message'] . "\n";
        print $err['file'] . " (" . $err['line'] . ")\n\n";
    }
});
