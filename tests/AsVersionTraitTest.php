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

namespace TinCanTest;

class AsVersionTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testTraitExists() {
        $this->assertTrue(trait_exists('TinCan\AsVersionTrait'));
    }

    public function testAsVersionReturnsArray() {
        $trait = $this->getMockForTrait('TinCan\AsVersionTrait');
        $this->assertInternalType('array', $trait->asVersion('test'));
    }

    public function testMagicSetThrowsException() {
        $this->setExpectedException('DomainException');
        $trait = $this->getMockForTrait('TinCan\AsVersionTrait');
        $trait->foo = 'bar';
    }
}
