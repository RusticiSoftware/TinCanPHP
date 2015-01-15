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

use TinCan\StatementRef;
use TinCan\Util;

class StatementRefTest extends PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $obj = new StatementRef();
        $this->assertInstanceOf('TinCan\StatementRef', $obj);
        $this->assertAttributeEmpty('id', $obj, 'id empty');
        $this->assertAttributeNotEmpty('objectType', $obj, 'objectType not empty');
    }

    public function testGetObjectType() {
        $obj = new StatementRef();
        $this->assertSame('StatementRef', $obj->getObjectType());
    }

    public function testId() {
        $obj = new StatementRef();
        $id  = Util::getUUID();
        $this->assertSame($obj, $obj->setId($id));
        $this->assertSame($id, $obj->getId());
    }

    public function testSetIdThrowsException() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'arg1 must be a UUID'
        );
        $obj = new StatementRef(['id' => 'foo']);
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $args = [
            'objectType' => 'StatementRef',
            'id' => Util::getUUID(),
        ];
        $obj = new StatementRef($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "version 1.0.0");
    }
}
