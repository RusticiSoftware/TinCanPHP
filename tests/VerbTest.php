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

use TinCan\Verb;

class VerbTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    static private $DISPLAY;

    static public function setUpBeforeClass() {
        self::$DISPLAY = [
            'en-US' => 'experienced',
            'en-GB' => 'experienced',
            'es' => 'experimentado',
            'fr' => 'expérimenté',
            'it' => 'esperto'
        ];
    }

    public function testInstantiation() {
        $obj = new Verb();
        $this->assertInstanceOf('TinCan\Verb', $obj);
        $this->assertAttributeEmpty('id', $obj, 'id empty');
        $this->assertAttributeInstanceOf('TinCan\LanguageMap', 'display', $obj, 'display is LanguageMap');
    }

    public function testFromJSONInvalidNull() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Verb::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Verb::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Verb::fromJSON('{id:"some value"}');
    }

    public function testFromJSONIDOnly() {
        $obj = Verb::fromJSON('{"id":"' . COMMON_VERB_ID . '"}');
        $this->assertInstanceOf('TinCan\Verb', $obj);
        $this->assertAttributeEquals(COMMON_VERB_ID, 'id', $obj, 'id matches');
        $this->assertTrue($obj->getDisplay()->isEmpty(), 'display empty');
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $args = [
            'id' => COMMON_VERB_ID,
            'display' => self::$DISPLAY
        ];

        $obj       = Verb::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Verb::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmptyLanguageMap() {
        $args      = ['display' => []];

        $obj       = Verb::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        unset($args['display']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmptyStringInLanguageMap() {
        $args      = ['display' => ['en' => '']];

        $obj       = Verb::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testCompareWithSignature() {
        $full = [
            'id' => COMMON_VERB_ID,
            'display' => self::$DISPLAY
        ];
        $display2 = array_replace(self::$DISPLAY, ['en-US' => 'not experienced']);
        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'id',
                'objArgs'     => ['id' => COMMON_VERB_ID]
            ],
            [
                'description' => 'display',
                'objArgs'     => ['display' => self::$DISPLAY]
            ],
            [
                'description' => 'all',
                'objArgs'     => $full
            ],

            //
            // display is not matched for signature purposes because it
            // is not supposed to affect the meaning of the statement
            //
            [
                'description' => 'display only: mismatch (allowed)',
                'objArgs'     => ['display' => self::$DISPLAY ],
                'sigArgs'     => ['display' => $display2 ]
            ],
            [
                'description' => 'full: display mismatch (allowed)',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['display' => $display2 ])
            ],

            [
                'description' => 'id only: mismatch',
                'objArgs'     => ['id' => COMMON_VERB_ID ],
                'sigArgs'     => ['id' => COMMON_VERB_ID . '/invalid' ],
                'reason'      => 'Comparison of id failed: value is not the same'
            ],
            [
                'description' => 'full: id mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['id' => COMMON_VERB_ID . '/invalid']),
                'reason'      => 'Comparison of id failed: value is not the same'
            ]
        ];
        $this->runSignatureCases("TinCan\Verb", $cases);
    }
}
