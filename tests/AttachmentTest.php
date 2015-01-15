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

use TinCan\Attachment;

class AttachmentTest extends PHPUnit_Framework_TestCase {
    const DISPLAY = 'testDisplay';

    private $emptyProperties = array(
        'usageType',
        'contentType',
        'length',
        'sha2',
        'fileUrl',
    );

    private $nonEmptyProperties = array(
        'display',
        'description',
    );

    public function testInstantiation() {
        $obj = new Attachment();
        $this->assertInstanceOf('TinCan\Attachment', $obj);
        foreach ($this->emptyProperties as $property) {
            $this->assertAttributeEmpty($property, $obj, "$property empty");
        }
        foreach ($this->nonEmptyProperties as $property) {
            $this->assertAttributeNotEmpty($property, $obj, "$property not empty");
        }
    }

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\Attachment'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\Attachment'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\Attachment'));
    }

    // TODO: need more robust test (happy-path)
    public function testAsVersion() {
        $args      = ['display' => [self::DISPLAY]];
        $obj       = new Attachment($args);
        $versioned = $obj->asVersion('test');

        $this->assertEquals($versioned, $args, "display only: test");
    }
}
