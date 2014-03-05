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

use TinCan\Group;

class GroupTest extends PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $obj = new Group();
        $this->assertInstanceOf('TinCan\Agent', $obj);
        $this->assertInstanceOf('TinCan\Group', $obj);
        $this->assertAttributeEquals([], 'member', $obj, 'member empty array');
    }

    // TODO: need to loop possible configs
    public function testFromJSONInstantiations() {
        $obj = Group::fromJSON('{"mbox":"' . COMMON_GROUP_MBOX . '", "member":[{"mbox":"' . COMMON_MBOX . '"}]}');
        $this->assertInstanceOf('TinCan\Group', $obj);
        $this->assertSame(COMMON_GROUP_MBOX, $obj->getMbox(), 'mbox value');
        $this->assertEquals([new TinCan\Agent(['mbox' => COMMON_MBOX])], $obj->getMember(), 'member list');
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $obj = new Group();
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals(
            [ 'objectType' => 'Group' ],
            $versioned,
            "empty: 1.0.0"
        );
    }

    public function testAddMember() {
        $common_agent = new TinCan\Agent(['mbox' => COMMON_MBOX]);

        $obj = new Group();

        $obj->addMember([ 'mbox' => COMMON_MBOX ]);
        $this->assertEquals([$common_agent], $obj->getMember(), 'member list create Agent');

        $obj->setMember([]);

        $obj->addMember($common_agent);
        $this->assertEquals([$common_agent], $obj->getMember(), 'member list existing Agent');
    }
}
