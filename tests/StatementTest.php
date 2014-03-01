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

use TinCan\Statement;

class StatementTest extends PHPUnit_Framework_TestCase {
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
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_NONE
        );
        $obj = Statement::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_NONE
        );
        $obj = Statement::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_SYNTAX
        );
        $obj = Statement::fromJSON('{id:"some value"}');
    }

    public function testStamp() {
        $obj = new Statement();
        $obj->stamp();

        $this->assertAttributeInternalType('string', 'timestamp', $obj, 'timestamp is string');
        $this->assertRegExp(UUID_V4_REGEX, $obj->getId(), 'id is UUId');
    }

    /*
    // TODO: need to loop possible configs
    public function testFromJSONInstantiations() {
        $obj = Statement::fromJSON('{"id":"' . COMMON_MBOX . '"}');
        $this->assertInstanceOf('TinCan\Statement', $obj);
        $this->assertSame(COMMON_MBOX, $obj->getMbox(), 'mbox value');
    }
    */

    // TODO: need to loop versions
    public function testAsVersion() {
        $obj = new Statement(
            [
                'actor' => [
                    'id' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new TinCan\Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        $obj->stamp();
        $versioned = $obj->asVersion('1.0.0');

        print json_encode($versioned);
    }
}
