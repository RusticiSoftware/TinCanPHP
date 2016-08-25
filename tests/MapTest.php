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

use TinCan\Map;

class StubMap extends Map {}

class MapTest extends \PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $obj = new StubMap();
    }

    public function testInstantiationWithArg() {
        $obj = new StubMap([]);
        $this->assertTrue($obj->isEmpty());
    }

    public function testSetUnset() {
        $obj = new StubMap();

        $code = 'code';
        $value = 'value';

        $obj->set($code, $value);

        $this->assertEquals($value, $obj->asVersion()[$code]);

        $obj->unset($code);

        $this->assertFalse(isset($obj->asVersion()[$code]));
    }

    public function testExceptionOnBadMethodCall() {
        $badName ="dsadasdasdasdasdasdas";

        $this->setExpectedException(
            '\BadMethodCallException',
            get_class(new StubMap) . "::$badName() does not exist"
        );

        $obj = new StubMap();
        $obj->$badName();
    }
}
