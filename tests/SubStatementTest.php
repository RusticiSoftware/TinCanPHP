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

use TinCan\SubStatement;

class SubStatementTest extends PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $obj = new SubStatement();
        $this->assertInstanceOf('TinCan\StatementBase', $obj);
    }

    public function testGetObjectType() {
        $obj = new SubStatement();
        $this->assertSame('SubStatement', $obj->getObjectType());
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $args = [
            'objectType' => 'SubStatement',
            'actor' => [
                'mbox' => COMMON_MBOX,
                'objectType' => 'Agent',
            ],
            'verb' => [
                'id' => COMMON_VERB_ID,
                'display' => [
                    'en-US' => 'experienced'
                ]
            ],
            'object' => [
                'objectType' => 'Activity',
                'id' => COMMON_ACTIVITY_ID,
                'definition' => [
                    'type' => 'Invalid type',
                    'name' => [
                        'en-US' => 'Test',
                    ],
                    //'description' => [
                        //'en-US' => 'Test description',
                    //],
                    'extensions' => [
                        'http://someuri' => 'some value'
                    ],
                ]
            ],
            'context' => [
                'contextActivities' => [
                    'parent' => [
                        [
                            'objectType' => 'Activity',
                            'id' => COMMON_ACTIVITY_ID . '/1',
                            'definition' => [
                                'name' => [
                                    'en-US' => 'Test: 1',
                                ],
                            ],
                        ]
                    ],
                ],
                'registration' => TinCan\Util::getUUID(),
            ],
            'result' => [
                'completion' => true,
                'success' => false,
                'score' => [
                    'raw' => '97',
                    'min' => '65',
                    'max' => '100',
                    'scaled' => '.97'
                ]
            ],
        ];
        $obj = new SubStatement($args);

        $obj->getTarget()->getDefinition()->getDescription()->set('en-ES', 'Testo descriptiono');
        $args['object']['definition']['description'] = ['en-ES' => 'Testo descriptiono'];

        $obj->getTarget()->getDefinition()->getName()->unset('en-US');
        unset($args['object']['definition']['name']);

        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($args, $versioned, 'version 1.0.0');
    }
}
