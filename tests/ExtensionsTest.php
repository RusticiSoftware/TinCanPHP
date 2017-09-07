<?php
/*
    Copyright 2015 Rustici Software

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

use TinCan\Extensions;

class ExtensionsTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    public function testInstantiation() {
        $obj = new Extensions();
        $this->assertInstanceOf('TinCan\Extensions', $obj);
    }

    public function testAsVersion() {
        $args = [];
        $args[COMMON_EXTENSION_ID_1] = 'test';
        $args[COMMON_EXTENSION_ID_2] = 'test2';

        $obj       = new Extensions($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "version: 1.0.0");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Extensions::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, null, "serialized version matches original");
    }

    public function testCompareWithSignature() {
        $success = ['success' => true, 'reason' => null];

        $content_1 = 'some value';
        $content_2 = 'some other value';

        $extensions1 = [ COMMON_EXTENSION_ID_1 => 'some value' ];
        $extensions2 = [ COMMON_EXTENSION_ID_1 => 'some value', COMMON_EXTENSION_ID_2 => 'some other value' ];
        $extensions3 = [ COMMON_EXTENSION_ID_2 => 'some other value' ];

        $cases = [
            [
                'description' => 'empty',
                'objArgs'     => []
            ],
            [
                'description' => 'single',
                'objArgs'     => $extensions1
            ],
            [
                'description' => 'multiple',
                'objArgs'     => $extensions2
            ],
            [
                'description' => 'empty sig: mismatch',
                'objArgs'     => $extensions1,
                'sigArgs'     => [],
                'reason'      => 'http://id.tincanapi.com/extension/topic not in signature'
            ],
            [
                'description' => 'empty this: mismatch',
                'objArgs'     => [],
                'sigArgs'     => $extensions1,
                'reason'      => 'http://id.tincanapi.com/extension/topic not in this'
            ],
            [
                'description' => 'missing in sig: mismatch',
                'objArgs'     => $extensions2,
                'sigArgs'     => $extensions3,
                'reason'      => 'http://id.tincanapi.com/extension/topic not in signature'
            ],
            [
                'description' => 'missing in this: mismatch',
                'objArgs'     => $extensions3,
                'sigArgs'     => $extensions2,
                'reason'      => 'http://id.tincanapi.com/extension/topic not in this'
            ],
            [
                'description' => 'single diff value in sig: mismatch',
                'objArgs'     => $extensions2,
                'sigArgs'     => array_replace($extensions2, [COMMON_EXTENSION_ID_1 => 'diff']),
                'reason'      => 'http://id.tincanapi.com/extension/topic does not match'
            ],
            [
                'description' => 'single diff value in this: mismatch',
                'objArgs'     => array_replace($extensions2, [COMMON_EXTENSION_ID_1 => 'diff']),
                'sigArgs'     => $extensions2,
                'reason'      => 'http://id.tincanapi.com/extension/topic does not match'
            ]
        ];
        $this->runSignatureCases("TinCan\Extensions", $cases);
    }
}
