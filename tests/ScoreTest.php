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

use TinCan\Score;

class ScoreTest extends PHPUnit_Framework_TestCase {
    private $emptyProperties = array(
        'scaled',
        'raw',
        'min',
        'max',
    );

    public function testInstantiation() {
        $obj = new Score();
        $this->assertInstanceOf('TinCan\Score', $obj);
        foreach ($this->emptyProperties as $property) {
            $this->assertAttributeEmpty($property, $obj, "$property empty");
        }
    }

    public function testUsesArraySetterTrait() {
        $obj = new Score();
        $this->assertTrue(method_exists($obj, '_fromArray'));
    }

    public function testUsesFromJSONTrait() {
        $obj = new Score();
        $this->assertTrue(method_exists($obj, 'fromJSON'));
    }

    public function testUsesAsVersionTrait() {
        $obj = new Score();
        $this->assertTrue(method_exists($obj, 'asVersion'));
    }

    // TODO: need more robust test (happy-path)
    public function testAsVersion() {
        $args      = ['raw' => 'test'];
        $obj       = new Score($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "raw only: 1.0.0");
    }
}
