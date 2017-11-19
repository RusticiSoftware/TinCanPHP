<?php
/*
    Copyright 2016 Rustici Software

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

use TinCan\State;
use TinCan\Group;

class StateTest extends \PHPUnit_Framework_TestCase {
    public function testCanSetActivityWithArray() {
        $args = [
            'id' => COMMON_ACTIVITY_ID,
            'definition' => []
        ];

        $state = new State();
        $state->setActivity($args);
    }

    public function testSetAgent() {
        $obj = new State();
        $obj->setAgent(['mbox' => COMMON_MBOX]);

        $this->assertInstanceOf('TinCan\Agent', $obj->getAgent());

        $group = new Group();
        $obj->setAgent(['objectType' => 'Group']);

        $this->assertInstanceOf('TinCan\Group', $obj->getAgent());
    }

    public function testExceptionOnInvalidRegistrationUUID() {
        $this->setExpectedException(
            "InvalidArgumentException",
            'arg1 must be a UUID'
        );

        $obj = new State();
        $obj->setRegistration('232....3.3..3./2/2/1m3m3m3');
    }
}
