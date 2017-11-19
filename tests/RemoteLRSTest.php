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

namespace TinCanTest;

use TinCan\Activity;
use TinCan\Agent;
use TinCan\Attachment;
use TinCan\Person;
use TinCan\RemoteLRS;
use TinCan\Statement;
use TinCan\StatementRef;
use TinCan\Util;
use TinCan\Verb;
use TinCan\Version;

class RemoteLRSTest extends \PHPUnit_Framework_TestCase {
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
        $this->assertSame(Version::latest(), $lrs->getVersion(), 'version set to latest');
    }

    public function testAbout() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->about();

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);
    }

    public function testSaveStatement() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );

        $response = $lrs->saveStatement($statement);
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertSame($response->content, $statement, 'content');
    }

    public function testSaveStatementStamped() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        $statement->stamp();

        $response = $lrs->saveStatement($statement);
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertSame($response->content, $statement, 'content');
    }

    public function testSaveStatements() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $statements = [
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ],
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/2'
                ])
            ],
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/3'
                ])
            ]
        ];

        $response = $lrs->saveStatements($statements);
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertTrue(is_array($response->content), 'content is array');
        $this->assertSame(count($response->content), 3, 'content has 3 values');
        foreach ($response->content as $i => $st) {
            $this->assertInstanceof('TinCan\Statement', $st, "$i: is statement");
            $id = $st->getId();
            $this->assertTrue(isset($id), "$i: id set");
        }
    }

    public function testSaveStatementsWithAttachments() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $attachment1 = [
            'usageType'   => 'http://id.tincanapi.com/attachment/supporting_media',
            'display'     => ['en-US' => 'RemoteLRSTest::testSaveStatements'],
            'contentType' => 'text/plain; charset=ascii',
            'content'     => 'Attachment 1 content created at: ' . Util::getTimestamp()
        ];
        $attachment2 = [
            'usageType'   => 'http://id.tincanapi.com/attachment/supporting_media',
            'display'     => ['en-US' => 'RemoteLRSTest::testSaveStatements'],
            'contentType' => 'text/plain; charset=ascii',
            'content'     => 'Attachment 2 content created at: ' . Util::getTimestamp()
        ];
        $statements = [
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID
                ]),
                'attachments' => [
                    $attachment1
                ]
            ],
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/2'
                ]),
                'attachments' => [
                    // provide a matching attachment to make sure only 2 in request
                    $attachment1,
                    $attachment2
                ]
            ]
        ];

        $response = $lrs->saveStatements($statements);
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertTrue(is_array($response->content), 'content is array');
        $this->assertSame(count($response->content), 2, 'content has 2 values');
        foreach ($response->content as $i => $st) {
            $this->assertInstanceof('TinCan\Statement', $st, "$i: is statement");
            $id = $st->getId();
            $this->assertTrue(isset($id), "$i: id set");
        }
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
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        if (! $saveResponse->success) {
            $this->fail("save statement setup failed: " . $saveResponse->content);
        }

        $response = $lrs->retrieveStatement($saveResponse->content->getId());

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);
        $this->assertInstanceOf('TinCan\Statement', $response->content);
    }

    public function testRetrieveStatementWithAttachments() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $content = json_encode(['foo' => 'bar']);

        $saveResponse = $lrs->saveStatement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID
                ]),
                'attachments' => [
                    new Attachment([
                        'usageType'   => 'http://id.tincanapi.com/attachment/supporting_media',
                        'display'     => ['en-US' => 'Test Display'],
                        'contentType' => 'application/json',
                        'content'     => $content,
                    ])
                ]
            ]
        );
        $this->assertTrue($saveResponse->success, 'save succeeded');
        if (! $saveResponse->success) {
            $this->fail("save statement setup failed: " . $saveResponse->content);
        }

        $response = $lrs->retrieveStatement($saveResponse->content->getId(), [ 'attachments' => true ]);

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);
        $this->assertInstanceOf('TinCan\Statement', $response->content);
        $this->assertTrue(count($response->content->getAttachments()) === 1, 'attachment count');
        $this->assertSame($content, $response->content->getAttachments()[0]->getContent(), 'attachment content');
    }

    public function testRetrieveVoidedStatement() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $saveResponse = $lrs->saveStatement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        if (! $saveResponse->success) {
            $this->fail("save statement setup failed: " . $saveResponse->content);
        }

        $voidResponse = $lrs->saveStatement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => Verb::Voided(),
                'object' => new StatementRef([
                    'id' => $saveResponse->content->getId()
                ])
            ]
        );
        if (! $voidResponse->success) {
            $this->fail("void statement setup failed: " . $voidResponse->content);
        }

        $retrieveResponse = $lrs->retrieveVoidedStatement($saveResponse->content->getId());

        $this->assertInstanceOf('TinCan\LRSResponse', $retrieveResponse);
        $this->assertTrue($retrieveResponse->success);
        $this->assertInstanceOf('TinCan\Statement', $retrieveResponse->content);
    }

    public function testQueryStatements() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->queryStatements(['limit' => 4]);

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertInstanceOf('TinCan\StatementsResult', $response->content);
    }

    public function testQueryStatementsWithAttachments() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->queryStatements(['limit' => 4, 'attachments' => true]);

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertInstanceOf('TinCan\StatementsResult', $response->content);
    }

    public function testMoreStatements() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $queryResponse = $lrs->queryStatements(['limit' => 1]);
        if (! $queryResponse->success) {
            $this->fail("query statements setup failed: " . $queryResponse->content);
        }

        if (! $queryResponse->content->getMore()) {
            $this->markTestSkipped('No more property in StatementsResult (not enough statements in endpoint?)');
        }

        $response = $lrs->moreStatements($queryResponse->content);

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertInstanceOf('TinCan\StatementsResult', $response->content, 'content');
    }

    public function testMoreStatementsWithAttachments() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $queryResponse = $lrs->queryStatements(['limit' => 1, 'attachments' => true]);
        if (! $queryResponse->success) {
            $this->fail("query statements setup failed: " . $queryResponse->content);
        }

        if (! $queryResponse->content->getMore()) {
            $this->markTestSkipped('No more property in StatementsResult (not enough statements in endpoint?)');
        }

        $response = $lrs->moreStatements($queryResponse->content);

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertInstanceOf('TinCan\StatementsResult', $response->content, 'content');
    }

    public function testRetrieveStateIds() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveStateIds(
            new Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            array(
                'registration' => Util::getUUID(),
                'since' => '2014-01-07T08:24:30Z'
            )
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testDeleteState() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteState(
            new Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey',
            [
                'registration' => Util::getUUID()
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testClearState() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->clearState(
            new Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            new Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            [
                'registration' => Util::getUUID()
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrieveActivityProfileIds() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveActivityProfileIds(
            new Activity(
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
            new Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
            ]
        );
        if (! $response->success) {
            $this->fail("save activity profile setup failed: " . $response->content);
        }

        $response = $lrs->retrieveActivityProfile(
            new Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testSaveActivityProfile() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->saveActivityProfile(
            new Activity(
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
            new Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrieveActivity() {
        $testActivity = new Activity([
            'id' => COMMON_ACTIVITY_ID. '/testRetrieveActivity',
            'definition' => [
                'name' => [
                    'en' => 'This is a test activity.'
                ]
            ]
        ]);

        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => $testActivity
            ]
        );
        $response = $lrs->saveStatement($statement);
        if (! $response->success) {
            $this->fail("save statement setup failed: " . $response->content);
        }

        $response = $lrs->retrieveActivity($testActivity->getId());
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertEquals($testActivity, $response->content, 'retrieved activity');
    }

    public function testRetrieveActivityWithHttpAcceptLanguageHeader() {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
        $this->testRetrieveActivity();
    }

    public function testRetrieveAgentProfileIds() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveAgentProfileIds(
            new Agent(
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
            new Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testDeleteAgentProfile() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteAgentProfile(
            new Agent(
                [ 'mbox' => COMMON_MBOX ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrievePerson() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $testAgent = new Agent(
            [
                'mbox' => COMMON_MBOX. '.testretrieveperson',
                'name' => COMMON_NAME
            ]
        );

        $testPerson = new Person(
            [
                'mbox' => [ COMMON_MBOX. '.testretrieveperson' ],
                'name' => [ COMMON_NAME ]
            ]
        );

        $response = $lrs->retrievePerson($testAgent);
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertEquals($testPerson, $response->content, 'retrieved person');
    }
}
