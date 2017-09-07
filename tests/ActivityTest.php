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

use TinCan\Activity;

class ActivityTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    static private $DEFINITION;

    static public function setUpBeforeClass() {
        self::$DEFINITION = [
            'type' => 'http://id.tincanapi.com/activitytype/unit-test',
            'name' => [
                'en-US' => 'test',
                'en-GB' => 'test',
                'es'    => 'prueba'
            ]
        ];
    }

    public function testInstantiation() {
        $obj = new Activity();
        $this->assertInstanceOf('TinCan\Activity', $obj);
        $this->assertAttributeEmpty('id', $obj, 'id empty');
        $this->assertAttributeEmpty('definition', $obj, 'definition empty');
    }

    public function testFromJSONInvalidNull() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Activity::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Activity::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Activity::fromJSON('{id:"some value"}');
    }

    public function testFromJSONIDOnly() {
        $obj = Activity::fromJSON('{"id":"' . COMMON_ACTIVITY_ID . '"}');
        $this->assertInstanceOf('TinCan\Activity', $obj);
        $this->assertAttributeEquals(COMMON_ACTIVITY_ID, 'id', $obj, 'id matches');
        $this->assertAttributeEmpty('definition', $obj, 'definition empty');
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $args = [
            'id' => COMMON_ACTIVITY_ID,
            'definition' => [
                'name' => [
                    'en' => 'test'
                ]
            ]
        ];

        $obj       = Activity::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Activity';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Activity::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Activity';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionIdOnly() {
        $args = [ 'id' => COMMON_ACTIVITY_ID ];

        $obj       = Activity::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Activity';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmptyDefinition() {
        $args = [
            'id' => COMMON_ACTIVITY_ID,
            'definition' => []
        ];

        $obj       = Activity::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Activity';
        unset($args['definition']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testCompareWithSignature() {
        $full = [
            'id' => COMMON_ACTIVITY_ID,
            'definition' => self::$DEFINITION
        ];
        $definition2 = array_replace(self::$DEFINITION, ['type' => 'http://id.tincanapi.com/activitytype/unit-test-suite']);
        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'id',
                'objArgs'     => ['id' => COMMON_ACTIVITY_ID]
            ],
            [
                'description' => 'definition',
                'objArgs'     => ['definition' => self::$DEFINITION]
            ],
            [
                'description' => 'all',
                'objArgs'     => $full
            ],

            //
            // definitions are not matched for signature purposes because they
            // are not supposed to affect the meaning of the statement and may
            // be supplied in canonical format, etc.
            //
            [
                'description' => 'definition only: mismatch (allowed)',
                'objArgs'     => ['definition' => self::$DEFINITION ],
                'sigArgs'     => ['definition' => $definition2 ]
            ],
            [
                'description' => 'full: definition mismatch (allowed)',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['definition' => $definition2 ])
            ],

            [
                'description' => 'id only: mismatch',
                'objArgs'     => ['id' => COMMON_ACTIVITY_ID ],
                'sigArgs'     => ['id' => COMMON_ACTIVITY_ID . '/invalid' ],
                'reason'      => 'Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'full: id mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['id' => COMMON_ACTIVITY_ID . '/invalid']),
                'reason'      => 'Comparison of id failed: value is not the same'
            ]
        ];
        $this->runSignatureCases("TinCan\Activity", $cases);
    }
}
