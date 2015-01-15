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

use TinCan\Context;
use TinCan\Util;

class ContextTest extends PHPUnit_Framework_TestCase {
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
                'objectType' => 'Agent',
                'name'       => 'test agent'
            ],
            'team' => [
                'objectType' => 'Group',
                'name'       => 'test group'
            ],
            'contextActivities' => [
                'category' => [
                    [
                        'objectType' => 'Activity',
                        'id'         => 'test category'
                    ]
                ]
            ],
            'revision'   => 'test revision',
            'platform'   => 'test platform',
            'language'   => 'test language',
            'statement'  => [
                'objectType' => 'StatementRef',
                'id'         => Util::getUUID()
            ],
            'extensions' => ['test extension'],
        ];

        $obj       = new Context($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "platform only: 1.0.0");
    }

    public function testSetInstructor() {
        $common_agent_cfg = [ 'mbox' => COMMON_MBOX ];
        $common_agent     = new TinCan\Agent($common_agent_cfg);
        $common_group_cfg = [ 'mbox' => COMMON_MBOX, 'objectType' => 'Group' ];
        $common_group     = new TinCan\Group($common_agent_cfg);

        $obj = new Context();

        $obj->setInstructor($common_agent_cfg);
        $this->assertEquals($common_agent, $obj->getInstructor(), "agent config");

        $obj->setInstructor(null);
        $this->assertEmpty($obj->getInstructor(), "empty");
    }
}
