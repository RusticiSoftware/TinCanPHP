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

use TinCan\Agent;
use TinCan\Context;
use TinCan\ContextActivities;
use TinCan\Extensions;
use TinCan\Group;
use TinCan\StatementRef;
use TinCan\Util;

class ContextTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    private $emptyProperties = array(
        'registration',
        'revision',
        'platform',
        'language',
    );

    private $nonEmptyProperties = array(
        'contextActivities',
        'extensions',
    );

    public function testInstantiation() {
        $obj = new Context();
        $this->assertInstanceOf('TinCan\Context', $obj);
        foreach ($this->emptyProperties as $property) {
            $this->assertAttributeEmpty($property, $obj, "$property empty");
        }
        foreach ($this->nonEmptyProperties as $property) {
            $this->assertAttributeNotEmpty($property, $obj, "$property not empty");
        }
    }

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\Context'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\Context'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\Context'));
    }

    /*
    // TODO: need to loop possible configs
    public function testFromJSONInstantiations() {
        $obj = Context::fromJSON('{"mbox":"' . COMMON_GROUP_MBOX . '", "member":[{"mbox":"' . COMMON_MBOX . '"}]}');
        $this->assertInstanceOf('TinCan\Context', $obj);
        $this->assertSame(COMMON_GROUP_MBOX, $obj->getMbox(), 'mbox value');
        $this->assertEquals([['mbox' => COMMON_MBOX]], $obj->getMember(), 'member list');
    }
    */

    public function testAsVersion() {
        $args = [
            'registration' => Util::getUUID(),
            'instructor'   => [
                'name' => 'test agent'
            ],
            'team' => [
                'name' => 'test group'
            ],
            'contextActivities' => [
                'category' => [
                    [
                        'id' => 'test category'
                    ]
                ]
            ],
            'revision'   => 'test revision',
            'platform'   => 'test platform',
            'language'   => 'test language',
            'statement'  => [
                'id' => Util::getUUID()
            ],
            'extensions' => [],
        ];
        $args['extensions'][COMMON_EXTENSION_ID_1] = "test";

        $obj       = Context::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['instructor']['objectType'] = 'Agent';
        $args['team']['objectType'] = 'Group';
        $args['contextActivities']['category'][0]['objectType'] = 'Activity';
        $args['statement']['objectType'] = 'StatementRef';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Context::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmptyLists() {
        $args = [
            'contextActivities' => [
                'category' => []
            ],
            'extensions' => [],
        ];

        $obj       = Context::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        unset($args['contextActivities']);
        unset($args['extensions']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testSetInstructor() {
        $common_agent_cfg = [ 'mbox' => COMMON_MBOX ];
        $common_agent     = new Agent($common_agent_cfg);
        $common_group_cfg = [ 'mbox' => COMMON_MBOX, 'objectType' => 'Group' ];
        $common_group     = new Group($common_agent_cfg);

        $obj = new Context();

        $obj->setInstructor($common_agent_cfg);
        $this->assertEquals($common_agent, $obj->getInstructor(), "agent config");

        $obj->setInstructor(null);
        $this->assertEmpty($obj->getInstructor(), "empty");
    }

    public function testCompareWithSignature() {
        $registration1 = Util::getUUID();
        $registration2 = Util::getUUID();
        $instructor1 = new Agent(
            [ 'mbox' => COMMON_MBOX ]
        );
        $instructor2 = new Agent(
            [ 'account' => [ 'homePage' => COMMON_ACCT_HOMEPAGE, 'name' => COMMON_ACCT_NAME ]]
        );
        $team1 = new Agent(
            [ 'mbox' => COMMON_MBOX ]
        );
        $team2 = new Agent(
            [ 'account' => [ 'homePage' => COMMON_ACCT_HOMEPAGE, 'name' => COMMON_ACCT_NAME ]]
        );
        $contextActivities1 = new ContextActivities(
            [ 'parent' => [ COMMON_ACTIVITY_ID ]]
        );
        $contextActivities2 = new ContextActivities(
            [ 'parent' => [ COMMON_ACTIVITY_ID . '/parent' ]],
            [ 'grouping' => [ COMMON_ACTIVITY_ID ]]
        );
        $ref1 = new StatementRef(
            [ 'id' => Util::getUUID() ]
        );
        $ref2 = new StatementRef(
            [ 'id' => Util::getUUID() ]
        );
        $extensions1 = new Extensions(
            [
                COMMON_EXTENSION_ID_1 => 'test1',
                COMMON_EXTENSION_ID_2 => 'test2'
            ]
        );
        $extensions2 = new Extensions(
            [
                COMMON_EXTENSION_ID_1 => 'test1'
            ]
        );

        $full = [
            'registration'      => $registration1,
            'instructor'        => $instructor1,
            'team'              => $team1,
            'contextActivities' => $contextActivities1,
            'revision'          => '1',
            'platform'          => 'mobile',
            'language'          => 'en-US',
            'statement'         => $ref1,
            'extensions'        => $extensions1
        ];

        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'registration',
                'objArgs'     => ['registration' => $registration1]
            ],
            [
                'description' => 'instructor',
                'objArgs'     => ['instructor' => $instructor1]
            ],
            [
                'description' => 'team',
                'objArgs'     => ['team' => $team1]
            ],
            [
                'description' => 'contextActivities',
                'objArgs'     => ['contextActivities' => $contextActivities1]
            ],
            [
                'description' => 'revision',
                'objArgs'     => ['revision' => '1']
            ],
            [
                'description' => 'platform',
                'objArgs'     => ['platform' => 'mobile']
            ],
            [
                'description' => 'language',
                'objArgs'     => ['language' => 'en-US']
            ],
            [
                'description' => 'statement',
                'objArgs'     => ['statement' => $ref1]
            ],
            [
                'description' => 'extensions',
                'objArgs'     => ['extensions' => $extensions1]
            ],
            [
                'description' => 'all',
                'objArgs'     => $full
            ],
            [
                'description' => 'registration only: mismatch',
                'objArgs'     => ['registration' => $registration1 ],
                'sigArgs'     => ['registration' => $registration2 ],
                'reason'      => 'Comparison of registration failed: value is not the same'
            ],
            [
                'description' => 'instructor only: mismatch',
                'objArgs'     => ['instructor' => $instructor1 ],
                'sigArgs'     => ['instructor' => $instructor2 ],
                'reason'      => 'Comparison of instructor failed: Comparison of mbox failed: value is not the same'
            ],
            [
                'description' => 'team only: mismatch',
                'objArgs'     => ['team' => $team1 ],
                'sigArgs'     => ['team' => $team2 ],
                'reason'      => 'Comparison of team failed: Comparison of mbox failed: value is not the same'
            ],
            [
                'description' => 'contextActivities only: mismatch',
                'objArgs'     => ['contextActivities' => $contextActivities1 ],
                'sigArgs'     => ['contextActivities' => $contextActivities2 ],
                'reason'      => 'Comparison of contextActivities failed: Comparison of parent failed: array lengths differ'
            ],
            [
                'description' => 'revision only: mismatch',
                'objArgs'     => ['revision' => '1' ],
                'sigArgs'     => ['revision' => '2' ],
                'reason'      => 'Comparison of revision failed: value is not the same'
            ],
            [
                'description' => 'platform only: mismatch',
                'objArgs'     => ['platform' => 'mobile' ],
                'sigArgs'     => ['platform' => 'desktop' ],
                'reason'      => 'Comparison of platform failed: value is not the same'
            ],
            [
                'description' => 'language only: mismatch',
                'objArgs'     => ['language' => 'en-US' ],
                'sigArgs'     => ['language' => 'en-GB' ],
                'reason'      => 'Comparison of language failed: value is not the same'
            ],
            [
                'description' => 'statement only: mismatch',
                'objArgs'     => ['statement' => $ref1 ],
                'sigArgs'     => ['statement' => $ref2 ],
                'reason'      => 'Comparison of statement failed: Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'extensions only: mismatch',
                'objArgs'     => ['extensions' => $extensions1 ],
                'sigArgs'     => ['extensions' => $extensions2 ],
                'reason'      => 'Comparison of extensions failed: http://id.tincanapi.com/extension/location not in signature'
            ],
            [
                'description' => 'full: registration mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['registration' => $registration2]),
                'reason'      => 'Comparison of registration failed: value is not the same'
            ],
            [
                'description' => 'full: instructor mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['instructor' => $instructor2]),
                'reason'      => 'Comparison of instructor failed: Comparison of mbox failed: value is not the same'
            ],
            [
                'description' => 'full: team mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['team' => $team2]),
                'reason'      => 'Comparison of team failed: Comparison of mbox failed: value is not the same'
            ],
            [
                'description' => 'full: contextActivities mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['contextActivities' => $contextActivities2]),
                'reason'      => 'Comparison of contextActivities failed: Comparison of parent failed: array lengths differ'
            ],
            [
                'description' => 'full: revision mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['revision' => '2']),
                'reason'      => 'Comparison of revision failed: value is not the same'
            ],
            [
                'description' => 'full: platform mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['platform' => 'desktop']),
                'reason'      => 'Comparison of platform failed: value is not the same'
            ],
            [
                'description' => 'full: language mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['language' => 'en-GB']),
                'reason'      => 'Comparison of language failed: value is not the same'
            ],
            [
                'description' => 'full: statement mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['statement' => $ref2]),
                'reason'      => 'Comparison of statement failed: Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'full: extensions mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['extensions' => $extensions2]),
                'reason'      => 'Comparison of extensions failed: http://id.tincanapi.com/extension/location not in signature'
            ]
        ];
        $this->runSignatureCases("TinCan\Context", $cases);
    }

    public function testSetInstructorConvertToGroup() {
        $obj = new Context();
        $obj->setInstructor(
            [
                'objectType' => 'Group'
            ]
        );
        $this->assertInstanceOf('TinCan\Group', $obj->getInstructor());
    }

    public function testSetRegistrationInvalidArgumentException() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'arg1 must be a UUID'
        );
        $obj = new Context();
        $obj->setRegistration('232....3.3..3./2/2/1m3m3m3');
    }
}
