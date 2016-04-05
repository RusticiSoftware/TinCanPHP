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
use TinCan\Context;
use TinCan\Result;
use TinCan\SubStatement;
use TinCan\Util;
use TinCan\Verb;

class SubStatementTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    public function testInstantiation() {
        $obj = new SubStatement();
        $this->assertInstanceOf('TinCan\StatementBase', $obj);
    }

    public function testGetObjectType() {
        $obj = new SubStatement();
        $this->assertSame('SubStatement', $obj->getObjectType());
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $args = [
            'timestamp' => '2015-01-28T14:23:37.159Z',
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
        ];

        $obj = SubStatement::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));

        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'SubStatement';
        $args['timestamp'] = $obj->getTimestamp();
        $args['actor']['objectType'] = 'Agent';
        $args['object']['objectType'] = 'Activity';
        $args['context']['contextActivities']['parent'][0]['objectType'] = 'Activity';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = SubStatement::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'SubStatement';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
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
        ];

        $obj = SubStatement::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'SubStatement';
        $args['actor']['objectType'] = 'Agent';
        $args['object']['objectType'] = 'Activity';
        unset($args['verb']['display']);
        unset($args['object']['definition']['name']);
        unset($args['object']['definition']['description']);
        unset($args['object']['definition']['extensions']);
        unset($args['context']['contextActivities']);
        unset($args['result']['score']);

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

        $obj = SubStatement::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'SubStatement';
        $args['actor']['objectType'] = 'Agent';
        $args['object']['objectType'] = 'Activity';
        unset($args['context']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }


    public function testCompareWithSignature() {
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

        $full = [
            'actor'     => $actor1,
            'verb'      => $verb1,
            'target'    => $activity1,
            'context'   => $context1,
            'result'    => $result1,
            'timestamp' => $timestamp1
        ];

        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
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
        ];
        $this->runSignatureCases("TinCan\SubStatement", $cases);
    }
}
