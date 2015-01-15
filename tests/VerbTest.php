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

use TinCan\Verb;

class VerbTest extends PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $obj = new Verb();
        $this->assertInstanceOf('TinCan\Verb', $obj);
        $this->assertAttributeEmpty('id', $obj, 'id empty');
        $this->assertAttributeInstanceOf('TinCan\LanguageMap', 'display', $obj, 'display is LanguageMap');
    }

    public function testFromJSONInvalidNull() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_NONE
        );
        $obj = Verb::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_NONE
        );
        $obj = Verb::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_SYNTAX
        );
        $obj = Verb::fromJSON('{id:"some value"}');
    }

    public function testFromJSONIDOnly() {
        $obj = Verb::fromJSON('{"id":"' . COMMON_VERB_ID . '"}');
        $this->assertInstanceOf('TinCan\Verb', $obj);
        $this->assertAttributeEquals(COMMON_VERB_ID, 'id', $obj, 'id matches');
        $this->assertTrue($obj->getDisplay()->isEmpty(), 'display empty');
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $args = [
            'id' => COMMON_VERB_ID,
            'display' => [
                'en-US' => 'Test display'
            ]
        ];
        $obj = new Verb($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "version 1.0.0");
    }
}
