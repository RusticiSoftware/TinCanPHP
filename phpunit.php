<?php

date_default_timezone_set('UTC');

require_once('tests/Constants.php');

require_once('src/LRSInterface.php');
require_once('src/StatementTargetInterface.php');
require_once('src/VersionableInterface.php');

require_once('src/ArraySetterTrait.php');
require_once('src/FromJSONTrait.php');
require_once('src/AsVersionTrait.php');

require_once('src/Map.php');
require_once('src/LRSResponse.php');

require_once('src/Util.php');
require_once('src/Version.php');
require_once('src/LanguageMap.php');
require_once('src/Extensions.php');
require_once('src/ActivityDefinition.php');
require_once('src/Activity.php');
require_once('src/AgentAccount.php');
require_once('src/Agent.php');
require_once('src/Group.php');
require_once('src/Verb.php');
require_once('src/ContextActivities.php');
require_once('src/Context.php');
require_once('src/Score.php');
require_once('src/Result.php');
require_once('src/StatementRef.php');
require_once('src/StatementBase.php');
require_once('src/SubStatement.php');
require_once('src/Statement.php');

require_once('src/StatementsResult.php');
require_once('src/About.php');
require_once('src/Document.php');
require_once('src/ActivityProfile.php');
require_once('src/AgentProfile.php');
require_once('src/State.php');

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
