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

use TinCan\Result;

class ResultTest extends PHPUnit_Framework_TestCase {
    private $emptyProperties = array(
        'success',
        'completion',
        'duration',
        'response',
        'score',
    );

    private $nonEmptyProperties = array(
        'extensions',
    );

    public function testInstantiation() {
        $obj = new Result();
        $this->assertInstanceOf('TinCan\Result', $obj);
        foreach ($this->emptyProperties as $property) {
            $this->assertAttributeEmpty($property, $obj, "$property empty");
        }
        foreach ($this->nonEmptyProperties as $property) {
            $this->assertAttributeNotEmpty($property, $obj, "$property not empty");
        }
    }

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\Result'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\Result'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\Result'));
    }

    // TODO: need more robust test (happy-path)
    public function testAsVersion() {
        $args      = ['success' => true];
        $obj       = new Result($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "success only: 1.0.0");
    }
}
