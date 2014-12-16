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

use TinCan\RemoteLRS;

class RemoteLRSTest extends PHPUnit_Framework_TestCase {
    static private $endpoint;
    static private $version;
    static private $username;
    static private $password;

    public static function setUpBeforeClass() {
        self::$endpoint = $GLOBALS['LRSs'][0]['endpoint'];
        self::$version = $GLOBALS['LRSs'][0]['version'];
        self::$username = $GLOBALS['LRSs'][0]['username'];
        self::$password = $GLOBALS['LRSs'][0]['password'];
    }

    public function testInstantiation() {
        $lrs = new RemoteLRS();
        $this->assertInstanceOf('TinCan\RemoteLRS', $lrs);
        $this->assertAttributeEmpty('endpoint', $lrs, 'endpoint empty');
        $this->assertAttributeEmpty('auth', $lrs, 'auth empty');
        $this->assertAttributeEmpty('extended', $lrs, 'extended empty');
        $this->assertSame(TinCan\Version::latest(), $lrs->getVersion(), 'version set to latest');
    }

    public function testAbout() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->about();

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testSaveStatement() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $statement = new TinCan\Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new TinCan\Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        //$statement->stamp();

        $response = $lrs->saveStatement($statement);
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrieveStatement() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $saveResponse = $lrs->saveStatement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new TinCan\Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        if ($saveResponse->success) {
            $response = $lrs->retrieveStatement($saveResponse->content->getId());

            $this->assertInstanceOf('TinCan\LRSResponse', $response);
            $this->assertTrue($response->success);
            $this->assertInstanceOf('TinCan\Statement', $response->content);
        }
        else {
            // TODO: skipped? throw?
        }
    }

    public function testQueryStatements() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->queryStatements(['limit' => 4]);

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertInstanceOf('TinCan\StatementsResult', $response->content);
    }

    public function testMoreStatements() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $queryResponse = $lrs->queryStatements(['limit' => 1]);

        if ($queryResponse->success) {
            if (! $queryResponse->content->getMore()) {
                $this->markTestSkipped('No more property in StatementsResult (not enough statements in endpoint?)');
            }

            $response = $lrs->moreStatements($queryResponse->content);

            $this->assertInstanceOf('TinCan\LRSResponse', $response);
            $this->assertInstanceOf('TinCan\StatementsResult', $response->content);
        }
        else {
            $this->markTestSkipped('Query to get "more" URL failed');
        }
    }

    public function testRetrieveStateIds() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveStateIds(
            new TinCan\Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new TinCan\Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            array(
                'registration' => TinCan\Util::getUUID(),
                'since' => '2014-01-07T08:24:30Z'
            )
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testDeleteState() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteState(
            new TinCan\Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new TinCan\Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey',
            [
                'registration' => TinCan\Util::getUUID()
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testClearState() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->clearState(
            new TinCan\Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new TinCan\Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            [
                'registration' => TinCan\Util::getUUID()
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrieveActivityProfileIds() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveActivityProfileIds(
            new TinCan\Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            array(
                'since' => '2014-01-07T08:24:30Z'
            )
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrieveActivityProfile() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->saveActivityProfile(
            new TinCan\Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
            ]
        );

        $response = $lrs->retrieveActivityProfile(
            new TinCan\Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testSaveActivityProfile() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->saveActivityProfile(
            new TinCan\Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testDeleteActivityProfile() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteActivityProfile(
            new TinCan\Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrieveAgentProfileIds() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveAgentProfileIds(
            new TinCan\Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            array(
                'since' => '2014-01-07T08:24:30Z'
            )
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrieveAgentProfile() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveAgentProfile(
            new TinCan\Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testDeleteAgentProfile() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteAgentProfile(
            new TinCan\Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }
}
