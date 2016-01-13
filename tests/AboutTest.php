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

use TinCan\About;

class AboutTest extends PHPUnit_Framework_TestCase {
    const VERSION_1 = '1.0.0';

    public function testInstantiation() {
        $obj = new About();
        $this->assertInstanceOf('TinCan\About', $obj);
        $this->assertAttributeEmpty('version', $obj, 'version empty');
        $this->assertAttributeNotEmpty('extensions', $obj, 'extenstions not empty');
    }

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\About'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\About'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\About'));
    }

    public function testVersion() {
        $obj     = new About();
        $version = [self::VERSION_1];
        $this->assertSame($obj, $obj->setVersion($version));
        $this->assertSame($version, $obj->getVersion());
    }

    public function testExtensionsWithArray() {
        $obj = new About();
        $this->assertSame($obj, $obj->setExtensions(['foo' => 'bar']));
        $this->assertInstanceOf('TinCan\Extensions', $obj->getExtensions());
        $this->assertFalse($obj->getExtensions()->isEmpty());
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $args      = ['version' => [self::VERSION_1]];
        $obj       = new About($args);
        $versioned = $obj->asVersion(self::VERSION_1);

        $this->assertEquals($versioned, $args, "version only: 1.0.0");
    }
}
