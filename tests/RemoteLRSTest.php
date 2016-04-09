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
        self::$version  = $GLOBALS['LRSs'][0]['version'];
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

        $options = $GLOBALS['LRSs'][0];
        $options['auth'] = "auth";

        $lrs = new RemoteLRS($options['endpoint'], $options['version'], $options['auth']);
        $this->assertEquals($options['version'], $lrs->getVersion());
        $this->assertEquals($options['endpoint'], $lrs->getEndpoint());
        $this->assertEquals($options['auth'], $lrs->getAuth());

        $lrs = new RemoteLRS($options);
        $this->assertEquals($options['version'], $lrs->getVersion());
        $this->assertEquals($options['endpoint'], $lrs->getEndpoint());
        $this->assertEquals($options['auth'], $lrs->getAuth());

        unset($options['auth'], $options['version']);

        $lrs = new RemoteLRS($options);
        $this->assertEquals(Version::latest(), $lrs->getVersion());
        $this->assertAttributeNotEmpty('auth', $lrs, 'auth empty');
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

        //Test that the statement gets updated.
        $savedStatement = $response->content;
        $savedStatement->setActor([ 'mbox' => 'mailto:info@tincanapi.com' ]);

        $response = $lrs->saveStatement($savedStatement);
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success, 'success');
        $this->assertSame($response->content, $savedStatement, 'content');
    }

    /**
     * This method is here to test how _parseHeaders()
     * will function with different structures of header arrays.
     *
     * At the moment the headers being tested are only those that
     * are returned by the endpoint we are testing against.
     */
    public function testParseHeaders()
    {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $expected = array(
            0 => "header",
            'content-type' => 'application/json',
            'folded' => "works\r\n\ttoo",
            'content-encoding' => 'gzip',
            'allow' => [
                'GET',
                'HEAD',
                'TRACE'
            ],
            'set-cookie' => [
                'foo=bar',
                'baz=quux'
            ],
            'accept-patch' => 'text/example;charset=utf-8',
            'folded2' => "works\r\n\tas well",
            'folded3' => "works"
        );

        $raw_headers = "Header\n"
            . "Content-Type: application/json\n"
            . "Folded: works\n\ttoo\n"
            . "Content-Encoding: gzip\n"
            . "Allow: GET\n"
            . "Allow: HEAD\n"
            . "Allow: TRACE\n"
            . "Set-Cookie: foo=bar\n"
            . "Set-Cookie: baz=quux\n"
            . "Accept-Patch: text/example;charset=utf-8\n"
            . "Folded2: works\n\tas well\n"
            . "Folded3: works\nas well\n";

        $method = new \ReflectionMethod("TinCan\RemoteLRS", "_parseHeaders");
        $method->setAccessible(true);

        $returnValue = $method->invoke($lrs, $raw_headers);

        foreach($expected as $key => $val) {
            $this->assertEquals(
                $expected[$key],
                $returnValue[$key],
                "The value of $key isn't the same in both arrays."
            );
        }

    }

    public function testQueryStatementsRequestParams()
    {

        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $expected = [
            'agent' => json_encode(['objectType' => 'Agent', 'name' => "A name"]),
            'verb' => COMMON_VERB_ID,
            'activity' => COMMON_ACTIVITY_ID,
            'ascending' => 'true',
            'related_activities' => 'false',
            'related_agents' => 'true',
            'attachments' => 'false',
            'registration' => Util::getUUID(),
            'since' => "2004-02-12T15:19:21+00:00",
            'until' => "2005-02-12T15:19:21+00:00",
            'limit' => 1,
            'format' => 'json'
        ];

        $input = $expected;
        $input['verb'] = new Verb(['id' => COMMON_VERB_ID]);
        $input['activity'] = new Activity(['id' => COMMON_ACTIVITY_ID]);
        $input['agent'] = new Agent(['objectType' => 'Agent', 'name' => "A name"]);
        $input['ascending'] = true;
        $input['related_activities'] = false;
        $input['related_agents'] = true;
        $input['attachments'] = false;

        $method = new \ReflectionMethod("TinCan\RemoteLRS", "_queryStatementsRequestParams");
        $method->setAccessible(true);

        $returnValue = $method->invoke($lrs, $input);

        foreach($expected as $k => $v) {
            $this->assertEquals($v, $returnValue[$k]);
        }
    }

    public function testSendRequestErrorOnFailure()
    {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $method = new \ReflectionMethod("TinCan\RemoteLRS", "sendRequest");
        $method->setAccessible(true);

        $response = $method->invoke($lrs, 'GET', 'http://23,3.33...32...../hi');
        $this->assertInstanceOf('TinCan\LRSResponse', $response);

        $expected = [];
        $expected['php5'] = "Request failed: exception 'ErrorException' with message 'fopen(): "
                   . "php_network_getaddresses: getaddrinfo failed: Nam";
        $expected['php7'] = "Request failed: ErrorException: fopen(): "
                   . "php_network_getaddresses: getaddrinfo failed: Name or service not known in";
        $expected['hhvm'] = "Request failed: exception 'ErrorException' with message 'Failed to open"
                   

        $foundMatch = false;

        foreach($expected as $versionNumber => $message) {
            if(strpos($response->content, $message) === 0) {
                $foundMatch = true;
                break;
            }
        }

        $this->assertTrue($foundMatch, $response->content);

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

        if ($queryResponse->success) {
            if (! $queryResponse->content->getMore()) {
                $this->markTestSkipped('No more property in StatementsResult (not enough statements in endpoint?)');
            }

            $response = $lrs->moreStatements($queryResponse->content);

            $this->assertInstanceOf('TinCan\LRSResponse', $response);
            $this->assertTrue($response->success, 'success');
            $this->assertInstanceOf('TinCan\StatementsResult', $response->content, 'content');
        }
        else {
            $this->markTestSkipped('Query to get "more" URL failed');
        }
    }

    public function testMoreStatementsWithAttachments() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $queryResponse = $lrs->queryStatements(['limit' => 1, 'attachments' => true]);

        if ($queryResponse->success) {
            if (! $queryResponse->content->getMore()) {
                $this->markTestSkipped('No more property in StatementsResult (not enough statements in endpoint?)');
            }

            $response = $lrs->moreStatements($queryResponse->content);

            $this->assertInstanceOf('TinCan\LRSResponse', $response);
            $this->assertTrue($response->success, 'success');
            $this->assertInstanceOf('TinCan\StatementsResult', $response->content, 'content');
        }
        else {
            $this->markTestSkipped('Query to get "more" URL failed');
        }
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

    public function testRetrieveStateIdsWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveStateIds(
            [ 'id' => COMMON_ACTIVITY_ID ],
            [ 'mbox' => COMMON_MBOX ],
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

    public function testDeleteStateWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteState(
            [ 'id' => COMMON_ACTIVITY_ID ],
            [ 'mbox' => COMMON_MBOX ],
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

    public function testClearStateWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->clearState(
            [ 'id' => COMMON_ACTIVITY_ID ],
            [ 'mbox' => COMMON_MBOX ],
            [
                'registration' => Util::getUUID()
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testSaveState()
    {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $id = Util::getUUID();
        $registration = Util::getUUID();
        $content = '';

        $response = $lrs->saveState(
            [ 'id' => COMMON_ACTIVITY_ID ],
            [ 'mbox' => COMMON_MBOX ],
            $id,
            $content,
            [
                'registration' => $registration,
                'contentType' => 'application/json',
            ]
        );
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);

        $response = $lrs->retrieveState(
            [ 'id' => COMMON_ACTIVITY_ID ],
            [ 'mbox' => COMMON_MBOX ],
            $response->content->getId(),
            [
                'registration' => $registration
            ]
        );
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);

        //Lets save again using the Etag
        $response = $lrs->saveState(
            [ 'id' => COMMON_ACTIVITY_ID ],
            [ 'mbox' => COMMON_MBOX ],
            $id,
            $content,
            [
                'registration' => $registration,
                'contentType' => 'application/json',
                'etag' => $response->httpResponse['headers']['etag']
            ]
        );
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);
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

    public function testRetrieveActivityProfileIdsWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveActivityProfileIds(
            [ 'id' => COMMON_ACTIVITY_ID ],
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

        $response = $lrs->retrieveActivityProfile(
            new Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey'
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testRetrieveActivityProfileWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->saveActivityProfile(
            [ 'id' => COMMON_ACTIVITY_ID ],
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
            ]
        );

        $response = $lrs->retrieveActivityProfile(
            [ 'id' => COMMON_ACTIVITY_ID ],
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
        $this->assertTrue($response->success);

        //Test that we can save again now that we have a value for etag.
        $response = $lrs->saveActivityProfile(
            new Activity(
                [ 'id' => COMMON_ACTIVITY_ID ]
            ),
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
                'etag' => $response->content->getEtag()
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);
    }

    public function testSaveActivityProfileWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->saveActivityProfile(
            [ 'id' => COMMON_ACTIVITY_ID ],
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);

        //Test that we can save again now that we have a value for etag.
        $response = $lrs->saveActivityProfile(
            [ 'id' => COMMON_ACTIVITY_ID ],
            'testKey',
            json_encode(['testProperty' => 'testValue']),
            [
                'contentType' => 'application/json',
                'etag' => $response->content->getEtag()
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);
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

    public function testDeleteActivityProfileWithArrayInput()  {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteActivityProfile(
            [ 'id' => COMMON_ACTIVITY_ID ],
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

        $response = $lrs->retrieveActivity($testActivity->getId());
        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertEquals($testActivity, $response->content, 'retrieved activity');
    }

    public function testRetrieveActivityWithHttpAcceptLanguageHeader() {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en_US';
        $this->testRetrieveActivity();
    }

    public function testRetrieveActivityWithArrayInput() {
        $testActivity = [
            'id' => COMMON_ACTIVITY_ID. '/testRetrieveActivity',
            'definition' => [
                'name' => [
                    'en' => 'This is a test activity.'
                ]
            ]
        ];

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

        $response = $lrs->retrieveActivity($testActivity['id']);
        $this->assertInstanceOf('TinCan\LRSResponse', $response);

        $this->assertEquals((new Activity($testActivity)), $response->content, 'retrieved activity');
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

    public function testRetrieveAgentProfileIdsWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->retrieveAgentProfileIds(
            [ 'mbox' => COMMON_MBOX ],
            array(
                'since' => '2014-01-07T08:24:30Z'
            )
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
    }

    public function testSaveAgentProfile()
    {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $content = '';
        $id = Util::getUUID();

        $response = $lrs->saveAgentProfile(
            [ 'mbox' => COMMON_MBOX ],
            $id,
            $content,
            [
                'contentType' => 'application/json'
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);

        $response = $lrs->retrieveAgentProfile(
            [ 'mbox' => COMMON_MBOX ],
            $id
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);

        //Try to save again now that we have a value for etag.
        $response = $lrs->saveAgentProfile(
            [ 'mbox' => COMMON_MBOX ],
            $id,
            $content,
            [
                'contentType' => 'application/json',
                'etag' => $response->httpResponse['headers']['etag']
            ]
        );

        $this->assertInstanceOf('TinCan\LRSResponse', $response);
        $this->assertTrue($response->success);
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

    public function testDeleteAgentProfileWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $response = $lrs->deleteAgentProfile(
            [ 'mbox' => COMMON_MBOX ],
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

    public function testRetrievePersonWithArrayInput() {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $testAgent = [
            'mbox' => COMMON_MBOX. '.testretrieveperson',
            'name' => COMMON_NAME
        ];

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

    public function testSetUnsupportedVersion()
    {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $value = ".dddwadwkdpoak";
        $this->setExpectedException(
            "InvalidArgumentException",
            "Unsupported version: $value"
        );
        $lrs->setVersion($value);
    }

    public function testSetEndpointWithoutTrailingSlash()
    {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $lrs->setEndpoint('https://a.com');
        $this->assertEquals('https://a.com/', $lrs->getEndpoint());
    }

    public function testSetAuth()
    {
        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);

        $user = "user";
        $pass = "pass";
        $combo = "Basic " . base64_encode($user . ':' . $pass);

        $lrs->setAuth($user);
        $this->assertEquals($user, $lrs->getAuth());

        $lrs->setAuth($pass);
        $this->assertEquals($pass, $lrs->getAuth());

        $lrs->setAuth($user, $pass);
        $this->assertEquals($combo, $lrs->getAuth());
    }

    public function testSetAuthWithNoInput()
    {
        $this->setExpectedException(
            "BadMethodCallException",
            "setAuth requires 1 or 2 arguments"
        );

        $lrs = new RemoteLRS(self::$endpoint, self::$version, self::$username, self::$password);
        $lrs->setAuth();
    }
}
