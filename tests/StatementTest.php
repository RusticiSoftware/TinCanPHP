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
use TinCan\Context;
use TinCan\Result;
use TinCan\Statement;
use TinCan\Util;
use TinCan\Verb;
use TinCan\Version;
use Namshi\JOSE\JWS;

class StatementTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    public function testInstantiation() {
        $obj = new Statement();
        $this->assertInstanceOf('TinCan\Statement', $obj);
        $this->assertAttributeEmpty('id', $obj, 'id empty');
        $this->assertAttributeEmpty('actor', $obj, 'actor empty');
        $this->assertAttributeEmpty('verb', $obj, 'verb empty');
        $this->assertAttributeEmpty('target', $obj, 'target empty');
        $this->assertAttributeEmpty('context', $obj, 'context empty');
        $this->assertAttributeEmpty('result', $obj, 'result empty');
        $this->assertAttributeEmpty('timestamp', $obj, 'timestamp empty');
        $this->assertAttributeEmpty('stored', $obj, 'stored empty');
        $this->assertAttributeEmpty('authority', $obj, 'authority empty');
        $this->assertAttributeEmpty('version', $obj, 'version empty');
    }

    public function testFromJSONInvalidNull() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Statement::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Statement::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Statement::fromJSON('{id:"some value"}');
    }

    public function testConstructionFromArrayWithId() {
        $id = Util::getUUID();
        $cfg = [
            'id' => $id,
            'actor' => [
                'mbox' => COMMON_MBOX,
            ],
            'verb' => [
                'id' => COMMON_VERB_ID,
            ],
            'object' => [
                'id' => COMMON_ACTIVITY_ID,
            ],
        ];
        $obj = new Statement($cfg);

        $this->assertSame($obj->getId(), $id, 'id');
    }

    public function testStamp() {
        $obj = new Statement();
        $obj->stamp();

        $this->assertAttributeInternalType('string', 'timestamp', $obj, 'timestamp is string');
        $this->assertRegExp(Util::UUID_REGEX, $obj->getId(), 'id is UUId');
    }

    public function testSetId() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'arg1 must be a UUID "some invalid id"'
        );

        $obj = new Statement();
        $obj->setId('some invalid id');
    }

    public function testSetStoredInvalidArgumentException() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'type of arg1 must be string or DateTime'
        );

        $obj = new Statement();
        $obj->setStored(1);
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $args = [
            'actor' => [
                'mbox' => COMMON_MBOX,
            ],
            'verb' => [
                'id' => COMMON_VERB_ID,
                'display' => [
                    'en-US' => 'experienced'
                ]
            ],
            'object' => [
                'id' => COMMON_ACTIVITY_ID,
                'definition' => [
                    'type' => 'Invalid type',
                    'name' => [
                        'en-US' => 'Test',
                    ],
                    'description' => [
                        'en-US' => 'Test description',
                    ],
                    'extensions' => [
                        'http://someuri' => 'some value'
                    ],
                ]
            ],
            'context' => [
                'contextActivities' => [
                    'parent' => [
                        [
                            'id' => COMMON_ACTIVITY_ID . '/1',
                            'definition' => [
                                'name' => [
                                    'en-US' => 'Test: 1',
                                ],
                            ],
                        ]
                    ],
                ],
                'registration' => Util::getUUID(),
            ],
            'result' => [
                'completion' => true,
                'success' => false,
                'score' => [
                    'raw' => '97',
                    'min' => '65',
                    'max' => '100',
                    'scaled' => '.97'
                ]
            ],
            'version' => '1.0.0',
            'attachments' => [
                [
                    'usageType'   => 'http://test',
                    'display'     => ['en-US' => 'test display'],
                    'contentType' => 'text/plain; charset=ascii',
                    'length'      => 0,
                    'sha2'        => hash('sha256', json_encode(['foo', 'bar']))
                ]
            ]
        ];

        $obj = Statement::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $obj->stamp();

        $versioned = $obj->asVersion('1.0.0');

        $args['id'] = $obj->getId();
        $args['timestamp'] = $obj->getTimestamp();
        $args['actor']['objectType'] = 'Agent';
        $args['object']['objectType'] = 'Activity';
        $args['context']['contextActivities']['parent'][0]['objectType'] = 'Activity';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Statement::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmptySubObjects() {
        $args = [
            'actor' => [
                'mbox' => COMMON_MBOX,
            ],
            'verb' => [
                'id' => COMMON_VERB_ID,
                'display' => []
            ],
            'object' => [
                'id' => COMMON_ACTIVITY_ID,
                'definition' => [
                    'type' => 'Invalid type',
                    'name' => [],
                    'description' => [],
                    'extensions' => [],
                ]
            ],
            'context' => [
                'contextActivities' => [
                    'parent' => [],
                ],
                'registration' => Util::getUUID(),
            ],
            'result' => [
                'completion' => true,
                'success' => false,
                'score' => []
            ],
            'version' => '1.0.0',
            'attachments' => []
        ];

        $obj = Statement::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['actor']['objectType'] = 'Agent';
        $args['object']['objectType'] = 'Activity';
        unset($args['verb']['display']);
        unset($args['object']['definition']['name']);
        unset($args['object']['definition']['description']);
        unset($args['object']['definition']['extensions']);
        unset($args['context']['contextActivities']);
        unset($args['result']['score']);
        unset($args['attachments']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionSubObjectWithEmptyValue() {
        $args = [
            'actor' => [
                'mbox' => COMMON_MBOX,
            ],
            'verb' => [
                'id' => COMMON_VERB_ID,
            ],
            'object' => [
                'id' => COMMON_ACTIVITY_ID,
                'definition' => [
                    'type' => 'Invalid type',
                    'name' => [
                        'en-US' => ''
                    ],
                ]
            ],
            'context' => [
                'contextActivities' => [],
            ],
            'result' => [
                'completion' => true,
                'success' => false,
                'score' => [
                    'raw' => 0
                ]
            ]
        ];

        $obj = Statement::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['actor']['objectType'] = 'Agent';
        $args['object']['objectType'] = 'Activity';
        unset($args['context']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testCompareWithSignature() {
        $id1 = Util::getUUID();
        $id2 = Util::getUUID();
        $actor1 = new Agent(
            [ 'mbox' => COMMON_MBOX ]
        );
        $actor2 = new Agent(
            [ 'account' => [ 'homePage' => COMMON_ACCT_HOMEPAGE, 'name' => COMMON_ACCT_NAME ]]
        );
        $verb1 = new Verb(
            [ 'id' => COMMON_VERB_ID ]
        );
        $verb2 = new Verb(
            [ 'id' => COMMON_VERB_ID . '/2' ]
        );
        $activity1 = new Activity(
            [ 'id' => COMMON_ACTIVITY_ID ]
        );
        $activity2 = new Activity(
            [ 'id' => COMMON_ACTIVITY_ID . '/2' ]
        );
        $context1 = new Context(
            [ 'registration' => Util::getUUID() ]
        );
        $context2 = new Context(
            [
                'contextActivities' => [
                    [ 'parent' => [ COMMON_ACTIVITY_ID . '/parent' ]],
                    [ 'grouping' => [ COMMON_ACTIVITY_ID ]]
                ]
            ]
        );
        $result1 = new Result(
            [ 'raw' => 87 ]
        );
        $result2 = new Result(
            [ 'response' => 'a' ]
        );
        $timestamp1           = '2015-01-28T14:23:37.159Z';
        $timestamp1_tz        = '2015-01-28T08:23:37.159-06:00';
        $timestamp1_subsecond = '2015-01-28T14:23:37.348Z';
        $timestamp2           = '2015-01-28T15:49:11.089Z';

        $attachments1 = new Attachment(
            [
                'usageType'   => 'http://id.tincanapi.com/attachment/supporting_media',
                'display'     => ['en-US' => 'Test Display'],
                'contentType' => 'application/json',
                'content'     => json_encode(['foo', 'bar']),
            ]
        );
        $attachments2 = new Attachment(
            [
                'usageType'   => 'http://id.tincanapi.com/attachment/supporting_media',
                'display'     => ['en-US' => 'Test Display'],
                'contentType' => 'application/json',
                'content'     => json_encode(['bar', 'foo']),
            ]
        );

        $full = [
            'id'          => $id1,
            'actor'       => $actor1,
            'verb'        => $verb1,
            'target'      => $activity1,
            'context'     => $context1,
            'result'      => $result1,
            'timestamp'   => $timestamp1,
            'attachments' => [ $attachments1 ]
        ];

        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'id',
                'objArgs'     => ['id' => $id1]
            ],
            [
                'description' => 'actor',
                'objArgs'     => ['actor' => $actor1]
            ],
            [
                'description' => 'verb',
                'objArgs'     => ['verb' => $verb1]
            ],
            [
                'description' => 'object',
                'objArgs'     => ['target' => $activity1]
            ],
            [
                'description' => 'result',
                'objArgs'     => ['result' => $result1]
            ],
            [
                'description' => 'context',
                'objArgs'     => ['context' => $context1]
            ],
            [
                'description' => 'timestamp',
                'objArgs'     => ['timestamp' => $timestamp1]
            ],
            [
                'description' => 'attachments',
                'objArgs'     => ['attachments' => [ $attachments1 ]]
            ],
            [
                'description' => 'all',
                'objArgs'     => $full
            ],

            //
            // special case where timestamp marks the same point in time but
            // is provided in a different timezone
            //
            [
                'description' => 'timestamp timezone difference',
                'objArgs'     => ['timestamp' => $timestamp1],
                'sigArgs'     => ['timestamp' => $timestamp1_tz]
            ],

            //
            // special case where we make sure sub-second precision is handled
            //
            [
                'description' => 'timestamp subsecond difference',
                'objArgs'     => ['timestamp' => $timestamp1],
                'sigArgs'     => ['timestamp' => $timestamp1_subsecond],
                'reason'      => 'Comparison of timestamp failed: value is not the same'
            ],

            [
                'description' => 'id this only: mismatch',
                'objArgs'     => ['id' => $id1],
                'sigArgs'     => [],
                'reason'      => 'Comparison of id failed: value not in signature'
            ],
            [
                'description' => 'id sig only: mismatch',
                'objArgs'     => [],
                'sigArgs'     => ['id' => $id1],
                'reason'      => 'Comparison of id failed: value not in this'
            ],
            [
                'description' => 'id only: mismatch',
                'objArgs'     => ['id' => $id1],
                'sigArgs'     => ['id' => $id2],
                'reason'      => 'Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'actor only: mismatch',
                'objArgs'     => ['actor' => $actor1],
                'sigArgs'     => ['actor' => $actor2],
                'reason'      => 'Comparison of actor failed: Comparison of mbox failed: value is not the same'
            ],
            [
                'description' => 'verb only: mismatch',
                'objArgs'     => ['verb' => $verb1],
                'sigArgs'     => ['verb' => $verb2],
                'reason'      => 'Comparison of verb failed: Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'object only: mismatch',
                'objArgs'     => ['target' => $activity1],
                'sigArgs'     => ['target' => $activity2],
                'reason'      => 'Comparison of target failed: Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'result only: mismatch',
                'objArgs'     => ['result' => $result1],
                'sigArgs'     => ['result' => $result2],
                'reason'      => 'Comparison of result failed: Comparison of response failed: value not present in this or signature'
            ],
            [
                'description' => 'context only: mismatch',
                'objArgs'     => ['context' => $context1],
                'sigArgs'     => ['context' => $context2],
                'reason'      => 'Comparison of context failed: Comparison of registration failed: value not present in this or signature'
            ],
            [
                'description' => 'timestamp only: mismatch',
                'objArgs'     => ['timestamp' => $timestamp1],
                'sigArgs'     => ['timestamp' => $timestamp2],
                'reason'      => 'Comparison of timestamp failed: value is not the same'
            ],
            [
                'description' => 'attachments this only: mismatch',
                'objArgs'     => ['attachments' => [$attachments1]],
                'sigArgs'     => ['attachments' => []],
                'reason'      => 'Comparison of attachments list failed: array lengths differ'
            ],
            [
                'description' => 'attachments sig only: mismatch',
                'objArgs'     => ['attachments' => []],
                'sigArgs'     => ['attachments' => [$attachments2]],
                'reason'      => 'Comparison of attachments list failed: array lengths differ'
            ],
            [
                'description' => 'attachments only: mismatch',
                'objArgs'     => ['attachments' => [$attachments1]],
                'sigArgs'     => ['attachments' => [$attachments2]],
                'reason'      => 'Comparison of attachment 0 failed: Comparison of sha2 failed: value is not the same'
            ],
            [
                'description' => 'full: id mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['id' => $id2]),
                'reason'      => 'Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'full: actor mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['actor' => $actor2]),
                'reason'      => 'Comparison of actor failed: Comparison of mbox failed: value is not the same'
            ],
            [
                'description' => 'full: verb mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['verb' => $verb2]),
                'reason'      => 'Comparison of verb failed: Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'full: target mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['target' => $activity2]),
                'reason'      => 'Comparison of target failed: Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'full: result mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['result' => $result2]),
                'reason'      => 'Comparison of result failed: Comparison of response failed: value not present in this or signature'
            ],
            [
                'description' => 'full: context mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['context' => $context2]),
                'reason'      => 'Comparison of context failed: Comparison of registration failed: value not present in this or signature'
            ],
            [
                'description' => 'full: timestamp mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['timestamp' => $timestamp2]),
                'reason'      => 'Comparison of timestamp failed: value is not the same'
            ],
            [
                'description' => 'full: attachments mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['attachments' => [$attachments2]]),
                'reason'      => 'Comparison of attachment 0 failed: Comparison of sha2 failed: value is not the same'
            ],
        ];
        $this->runSignatureCases("TinCan\Statement", $cases);
    }

    public function testHasAttachments() {
        $stNoAttachments = new Statement();
        $this->assertFalse($stNoAttachments->hasAttachments());

        $stWithAttachments = new Statement(
            [
                'attachments' => [
                    [
                        'usageType'   => 'http://test',
                        'display'     => ['en-US' => 'test display'],
                        'contentType' => 'text/plain; charset=ascii',
                        'length'      => 0,
                        'sha2'        => hash('sha256', json_encode(['foo', 'bar']))
                    ]
                ]
            ]
        );
        $this->assertTrue($stWithAttachments->hasAttachments());
    }

    public function testHasAttachmentWithContent() {
        $content = 'Just some test content';

        $stNoAttachments = new Statement();
        $this->assertFalse($stNoAttachments->hasAttachmentsWithContent());

        $stWithAttachmentNoContent = new Statement(
            [
                'attachments' => [
                    [
                        'usageType'   => 'http://test',
                        'display'     => ['en-US' => 'test display'],
                        'contentType' => 'text/plain; charset=ascii',
                        'length'      => strlen($content),
                        'sha2'        => hash('sha256', $content)
                    ]
                ]
            ]
        );
        $this->assertFalse($stWithAttachmentNoContent->hasAttachmentsWithContent());

        $stWithAttachmentWithContent = new Statement(
            [
                'attachments' => [
                    [
                        'usageType'   => 'http://test',
                        'display'     => ['en-US' => 'test display'],
                        'contentType' => 'text/plain; charset=ascii',
                        'length'      => strlen($content),
                        'sha2'        => hash('sha256', $content),
                        'content'     => $content
                    ]
                ]
            ]
        );
        $this->assertTrue($stWithAttachmentWithContent->hasAttachmentsWithContent());
    }

    public function testSignNoArgs() {
        $obj = new Statement();

        $this->setExpectedException(
            'PHPUnit_Framework_Error_Warning',
            (getenv('TRAVIS_PHP_VERSION') == "hhvm" ? 'sign() expects at least 2 parameters, 0 given' : 'Missing argument 1')
        );
        $obj->sign();
    }

    public function testSignOneArg() {
        $obj = new Statement();

        $this->setExpectedException(
            'PHPUnit_Framework_Error_Warning',
            (getenv('TRAVIS_PHP_VERSION') == "hhvm" ? 'sign() expects at least 2 parameters, 1 given' : 'Missing argument 2')
        );
        $obj->sign('test');
    }

    public function testSignNoActor() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'actor must be present in signed statement'
        );

        $obj = new Statement();
        $obj->sign('test', 'test');
    }

    public function testSignNoVerb() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'verb must be present in signed statement'
        );

        $obj = new Statement(
            [
                'actor' => ['mbox' => COMMON_MBOX]
            ]
        );
        $obj->sign('test', 'test');
    }

    public function testSignNoObject() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'object must be present in signed statement'
        );

        $obj = new Statement(
            [
                'actor' => ['mbox' => COMMON_MBOX],
                'verb' => [ 'id' => COMMON_VERB_ID ]
            ]
        );
        $obj->sign('test', 'test');
    }

    public function testSignInvalidAlgorithm() {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Invalid signing algorithm: 'not right'"
        );

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ])
            ]
        );
        $obj->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password'], ['algorithm' => 'not right']);
    }

    public function testSignEmptyPassword() {
        $this->setExpectedException(
            'Exception',
            'Unable to get private key: error:0906A068:PEM routines:PEM_do_header:bad password read'
        );

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ])
            ]
        );
        $obj->sign('file://' . $GLOBALS['KEYs']['private'], '');
    }

    public function testSignInvalidPassword() {
        $this->setExpectedExceptionRegExp(
            'Exception',
            '/Unable to get private key: error:.*:bad decrypt/'
        );

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ])
            ]
        );
        $obj->sign('file://' . $GLOBALS['KEYs']['private'], 'notthecorrectpasswordhopefully');
    }

    public function testSignInvalidX5cErrorToException() {
        $this->setExpectedExceptionRegExp(
            'PHPUnit_Framework_Error',
            '/supplied parameter cannot be coerced into an X509 certificate!/'
        );

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ])
            ]
        );
        $obj->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password'], [ 'x5c' => 'invalid' ]);
    }

    public function testSignInvalidX5cNoError() {
        $this->setExpectedExceptionRegExp(
            'Exception',
            '/Unable to read certificate for x5c inclusion: .*/'
        );

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ])
            ]
        );
        @$obj->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password'], [ 'x5c' => 'invalid' ]);
    }

    public function testVerifyNoSignature() {
        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignAndVerify'
                ])
            ]
        );
        $result = $obj->verify();

        $this->assertFalse($result['success'], 'success');
        $this->assertSame($result['reason'], 'Unable to locate signature attachment (usage type)', 'reason');
    }

    public function testVerifyInvalidJWS() {
        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignAndVerify'
                ]),
                'attachments' => [
                    [
                        'usageType'   => 'http://adlnet.gov/expapi/attachments/signature',
                        'display'     => ['en-US' => 'test display'],
                        'contentType' => 'application/octet-stream',
                        'content'     => 'not a signature'
                    ]
                ]
            ]
        );
        $result = $obj->verify();

        $this->assertFalse($result['success'], 'success');
        $this->assertStringStartsWith(
            'Failed to load JWS',
            $result['reason'],
            'reason'
        );
    }

    public function testVerifyInvalidX5cErrorToException() {
        $this->setExpectedExceptionRegExp(
            'PHPUnit_Framework_Error',
            '/supplied parameter cannot be coerced into an X509 certificate!/'
        );

        $content = new JWS(
            [
                'alg' => 'RS256',
                'x5c' => ['notAValidCertificate']
            ]
        );
        $content->setPayload(['prop' => 'val'], false);
        $content->sign(openssl_pkey_get_private('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password']));

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ]),
                'attachments' => [
                    [
                        'usageType'   => 'http://adlnet.gov/expapi/attachments/signature',
                        'display'     => ['en-US' => 'test display'],
                        'contentType' => 'application/octet-stream',
                        'content'     => $content->getTokenString()
                    ]
                ]
            ]
        );
        $result = $obj->verify();
    }

    public function testVerifyInvalidX5cNoError() {
        $content = new JWS(
            [
                'alg' => 'RS256',
                'x5c' => ['notAValidCertificate']
            ]
        );
        $content->setPayload(['prop' => 'val'], false);
        $content->sign(openssl_pkey_get_private('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password']));

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ]),
                'attachments' => [
                    [
                        'usageType'   => 'http://adlnet.gov/expapi/attachments/signature',
                        'display'     => ['en-US' => 'test display'],
                        'contentType' => 'application/octet-stream',
                        'content'     => $content->getTokenString()
                    ]
                ]
            ]
        );
        @$result = $obj->verify();

        $this->assertFalse($result['success'], 'success');
        $this->assertSame('failed to read cert in x5c: error:0906D06C:PEM routines:PEM_read_bio:no start line', $result['reason'], 'reason');
    }

    public function testVerifyNoPubKey() {
        $content = new JWS(
            [
                'alg' => 'RS256'
            ]
        );
        $content->setPayload(['prop' => 'val'], false);
        $content->sign(openssl_pkey_get_private('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password']));

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ]),
                'attachments' => [
                    [
                        'usageType'   => 'http://adlnet.gov/expapi/attachments/signature',
                        'display'     => ['en-US' => 'test display'],
                        'contentType' => 'application/octet-stream',
                        'content'     => $content->getTokenString()
                    ]
                ]
            ]
        );
        $result = $obj->verify();

        $this->assertFalse($result['success'], 'success');
        $this->assertSame($result['reason'], 'No public key found or provided for verification', 'reason');
    }

    public function testVerifyIncorrectPubKey() {
        $content = new JWS(
            [
                'alg' => 'RS256'
            ]
        );
        $content->setPayload(['prop' => 'val'], false);
        $content->sign(openssl_pkey_get_private('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password']));

        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignNoPassword'
                ]),
                'attachments' => [
                    [
                        'usageType'   => 'http://adlnet.gov/expapi/attachments/signature',
                        'display'     => ['en-US' => 'test display'],
                        'contentType' => 'application/octet-stream',
                        'content'     => $content->getTokenString()
                    ]
                ]
            ]
        );

        $newKey = openssl_pkey_new(
            [
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA
            ]
        );
        $pubKey = openssl_pkey_get_details($newKey);
        $pubKey = $pubKey["key"];

        $result = $obj->verify(['publicKey' => $pubKey]);

        $this->assertFalse($result['success'], 'success');
        $this->assertSame($result['reason'], 'Failed to verify signature', 'reason');
    }

    public function testVerifyDiffStatement() {
        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testVerifyDiffStatement'
                ])
            ]
        );
        $obj->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password'], ['x5c' => 'file://' . $GLOBALS['KEYs']['public']]);

        $diff = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testVerifyDiffStatement-diff'
                ]),
                'attachments' => $obj->getAttachments()
            ]
        );
        $result = $diff->verify();

        $this->assertFalse($result['success'], 'success');
        $this->assertSame($result['reason'], 'Statement to signature comparison failed: Comparison of target failed: Comparison of id failed: value is not the same', 'reason');
    }

    public function testSignAndVerify() {
        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignAndVerify'
                ])
            ]
        );
        $obj->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password']);

        $attachment = $obj->getAttachments()[0];

        $this->assertSame($attachment->getUsageType(), 'http://adlnet.gov/expapi/attachments/signature', 'usage type value');
        $this->assertSame($attachment->getContentType(), 'application/octet-stream', 'content type value');

        $result = $obj->verify(['publicKey' => 'file://' . $GLOBALS['KEYs']['public']]);
        if (! $result['success']) {
            print $result['reason'];
        }
        $this->assertTrue($result['success'], 'success return value');
    }

    public function testSignAndVerifyFromEmbedded() {
        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignAndVerifyFromEmbedded'
                ])
            ]
        );
        $obj->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password'], [ 'x5c' => 'file://' . $GLOBALS['KEYs']['public'] ]);

        $attachment = $obj->getAttachments()[0];

        $this->assertSame($attachment->getUsageType(), 'http://adlnet.gov/expapi/attachments/signature', 'usage type value');
        $this->assertSame($attachment->getContentType(), 'application/octet-stream', 'content type value');

        $result = $obj->verify();
        if (! $result['success']) {
            print $result['reason'];
        }
        $this->assertTrue($result['success'], 'success return value');
    }

    public function testSignAndVerifyNoPubKey() {
        $obj = new Statement(
            [
                'actor' => [ 'mbox' => COMMON_MBOX ],
                'verb' => [ 'id' => COMMON_VERB_ID ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementTest/testSignAndVerify'
                ])
            ]
        );
        $obj->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password']);

        $result = $obj->verify();
        $this->assertFalse($result['success'], 'success return value');
        $this->assertSame($result['reason'], 'No public key found or provided for verification', 'reason');
    }
}
