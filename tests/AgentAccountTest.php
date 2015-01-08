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

use TinCan\AgentAccount;

class AgentAccountTest extends PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $obj = new AgentAccount();
        $this->assertInstanceOf('TinCan\AgentAccount', $obj);
        $this->assertAttributeEmpty('homePage', $obj, 'homePage empty');
        $this->assertAttributeEmpty('name', $obj, 'name empty');
    }

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\AgentAccount'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\AgentAccount'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\AgentAccount'));
    }

    public function testName() {
        $obj  = new AgentAccount();
        $name = 'test';
        $this->assertSame($obj, $obj->setName($name));
        $this->assertSame($name, $obj->getName());
    }

    public function testHomePage() {
        $obj      = new AgentAccount();
        $homePage = 'http://tincanapi.com';
        $this->assertSame($obj, $obj->setHomePage($homePage));
        $this->assertSame($homePage, $obj->getHomePage());
    }

    public function testAsVersion() {
        $args      = ['name' => 'test', 'homePage' => 'http://tincanapi.com'];
        $obj       = new AgentAccount($args);
        $versioned = $obj->asVersion('test');

        $this->assertEquals($versioned, $args, "all properties: test");
    }
}
