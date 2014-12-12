<?php
/*
    Copyright 2014 Rustici Software

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

require_once( 'TinCanApi_Autoloader.php' );

class RemoteLRSTest extends require_once( 'TinCanApi_Autoloader.php' ); {
    static private $endpoint = 'http://cloud.scorm.com/tc/3HYPTQLAI9/sandbox';
    static private $version  = '1.0.1';
    static private $username = '';
    static private $password = '';

    public function testInstantiation() {
        $lrs = new TinCanAPI_RemoteLRS();
        $this->assertInstanceOf('TinCanAPI_RemoteLRS', $lrs);
        $this->assertAttributeEmpty('endpoint', $lrs, 'endpoint empty');
        $this->assertAttributeEmpty('auth', $lrs, 'auth empty');
        $this->assertAttributeEmpty('extended', $lrs, 'extended empty');
        $this->assertSame(TinCanAPI_Version::latest(), $lrs->getVersion(), 'version set to latest');
    }

    public function testAbout() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->about();

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testSaveStatement() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $statement = new TinCanAPI_Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new TinCanAPI_Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        //$statement->stamp();

        $response = $lrs->saveStatement($statement);
        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testRetrieveStatement() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $saveResponse = $lrs->saveStatement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new TinCanAPI_Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        if ($saveResponse->success) {
            $response = $lrs->retrieveStatement($saveResponse->content->getId());

            $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
            $this->assertTrue($response->success);
            $this->assertInstanceOf('TinCanAPI_Statement', $response->content);
        }
        else {
            // TODO: skipped? throw?
        }
    }

    public function testQueryStatements() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->queryStatements(['limit' => 4]);

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
        $this->assertInstanceOf('TinCanAPI_StatementsResult', $response->content);
    }

    public function testMoreStatements() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $queryResponse = $lrs->queryStatements(['limit' => 4]);

        if ($queryResponse->success) {
            $response = $lrs->moreStatements($queryResponse->content);

            $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
            $this->assertInstanceOf('TinCanAPI_StatementsResult', $response->content);
        }
        else {
            // TODO: skipped? throw?
        }
    }

    public function testRetrieveStateIds() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveStateIds(
            new TinCanAPI_Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new TinCanAPI_Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            array(
                'registration' => TinCanAPI_Util::getUUID(),
                'since' => '2014-01-07T08:24:30Z'
            )
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testDeleteState() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteState(
            new TinCanAPI_Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new TinCanAPI_Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey',
            [
                'registration' => TinCanAPI_Util::getUUID()
            ]
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testClearState() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->clearState(
            new TinCanAPI_Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new TinCanAPI_Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            [
                'registration' => TinCanAPI_Util::getUUID()
            ]
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testRetrieveActivityProfileIds() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveActivityProfileIds(
            new TinCanAPI_Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            array(
                'since' => '2014-01-07T08:24:30Z'
            )
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testRetrieveActivityProfile() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->saveActivityProfile(
            new TinCanAPI_Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
            ]
        );

        $response = $lrs->retrieveActivityProfile(
            new TinCanAPI_Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testSaveActivityProfile() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->saveActivityProfile(
            new TinCanAPI_Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
            ]
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testDeleteActivityProfile() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteActivityProfile(
            new TinCanAPI_Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testRetrieveAgentProfileIds() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveAgentProfileIds(
            new TinCanAPI_Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            array(
                'since' => '2014-01-07T08:24:30Z'
            )
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testRetrieveAgentProfile() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveAgentProfile(
            new TinCanAPI_Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }

    public function testDeleteAgentProfile() {
        $lrs = new TinCanAPI_RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteAgentProfile(
            new TinCanAPI_Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCanAPI_LRSResponse', $response);
    }
}
