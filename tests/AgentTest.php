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

use TinCan\Agent;

class AgentTest extends PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $obj = new Agent();
        $this->assertInstanceOf('TinCan\Agent', $obj);
        $this->assertAttributeEmpty('name', $obj, 'name empty');
        $this->assertAttributeEmpty('mbox', $obj, 'mbox empty');
        $this->assertAttributeEmpty('mbox_sha1sum', $obj, 'mbox_sha1sum empty');
        $this->assertAttributeEmpty('openid', $obj, 'openid empty');
        $this->assertAttributeEmpty('account', $obj, 'account empty');
    }

    public function testFromJSONInvalidNull() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_NONE
        );
        $obj = Agent::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_NONE
        );
        $obj = Agent::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_SYNTAX
        );
        $obj = Agent::fromJSON('{name:"some value"}');
    }

    // TODO: need to loop possible configs
    public function testFromJSONInstantiations() {
        $obj = Agent::fromJSON('{"mbox":"' . COMMON_MBOX . '"}');
        $this->assertInstanceOf('TinCan\Agent', $obj);
        $this->assertSame(COMMON_MBOX, $obj->getMbox(), 'mbox value');
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $obj = new Agent(
            [ 'mbox' => COMMON_MBOX ]
        );
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals(
            [ 'objectType' => 'Agent', 'mbox' => COMMON_MBOX ],
            $versioned,
            "mbox only: 1.0.0"
        );
    }

    public function testSetMbox() {
        $obj = new Agent();

        $obj->setMbox(COMMON_MBOX);
        $this->assertSame(COMMON_MBOX, $obj->getMbox());

        $obj->setMbox(COMMON_EMAIL);
        $this->assertSame(COMMON_MBOX, $obj->getMbox());

        //
        // make sure it doesn't add mailto when null
        //
        $obj->setMbox(null);
        $this->assertAttributeEmpty('mbox', $obj);
    }
}
