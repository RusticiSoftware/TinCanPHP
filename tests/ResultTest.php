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

use TinCan\Extensions;
use TinCan\Result;
use TinCan\Score;

class ResultTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

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
        $args      = [
            'success'    => true,
            'completion' => true,
            'duration'   => 'PT15S',
            'response'   => 'a',
            'score'      => [
                'raw'    => 100,
                'scaled' => 1,
                'min'    => 0,
                'max'    => 100
            ]
        ];
        $obj       = new Result($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Result::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionScoreEmpty() {
        $args = [
            'score' => []
        ];

        $obj       = Result::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        unset($args['score']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionScoreZeroRaw() {
        $args = [
            'score' => [
                'raw' => 0,
            ]
        ];

        $obj       = Result::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionResponseEmptyString() {
        $args = [
            'response' => ''
        ];

        $obj       = Result::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionDurationEmptyString() {
        $args = [
            'duration' => ''
        ];

        $obj       = Result::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        unset($args['duration']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testCompareWithSignature() {
        $score1 = new Score(
            [
                'raw'    => 97,
                'scaled' => 0.97,
                'min'    => 0,
                'max'    => 100
            ]
        );
        $score2 = new Score(
            [
                'raw'    => 15,
                'scaled' => 0.50
            ]
        );
        $extensions1 = new Extensions(
            [
                COMMON_EXTENSION_ID_1 => 'test1',
                COMMON_EXTENSION_ID_2 => 'test2'
            ]
        );
        $extensions2 = new Extensions(
            [
                COMMON_EXTENSION_ID_1 => 'test1'
            ]
        );

        $full = [
            'success'    => true,
            'completion' => true,
            'duration'   => 'PT15S',
            'response'   => 'a',
            'score'      => $score1,
            'extensions' => $extensions1
        ];

        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'success',
                'objArgs'     => ['success' => true]
            ],
            [
                'description' => 'completion',
                'objArgs'     => ['completion' => true]
            ],
            [
                'description' => 'duration',
                'objArgs'     => ['duration' => 'PT15S']
            ],
            [
                'description' => 'response',
                'objArgs'     => ['response' => 'a']
            ],
            [
                'description' => 'score',
                'objArgs'     => ['score' => $score1]
            ],
            [
                'description' => 'extensions',
                'objArgs'     => ['extensions' => $extensions1]
            ],
            [
                'description' => 'all',
                'objArgs'     => [
                    'success'    => false,
                    'completion' => false,
                    'duration'   => 'PT15S',
                    'response'   => 'b',
                    'score'      => $score2,
                    'extensions' => $extensions2
                ]
            ],
            [
                'description' => 'success only: mismatch',
                'objArgs'     => ['success' => true ],
                'sigArgs'     => ['success' => false ],
                'reason'      => 'Comparison of success failed: value is not the same'
            ],
            [
                'description' => 'completion only: mismatch',
                'objArgs'     => ['completion' => true ],
                'sigArgs'     => ['completion' => false ],
                'reason'      => 'Comparison of completion failed: value is not the same'
            ],
            [
                'description' => 'duration only: mismatch',
                'objArgs'     => ['duration' => 'PT15S' ],
                'sigArgs'     => ['duration' => 'PT180S' ],
                'reason'      => 'Comparison of duration failed: value is not the same'
            ],
            [
                'description' => 'response only: mismatch',
                'objArgs'     => ['response' => 'a' ],
                'sigArgs'     => ['response' => 'b' ],
                'reason'      => 'Comparison of response failed: value is not the same'
            ],
            [
                'description' => 'score only: mismatch',
                'objArgs'     => ['score' => $score1 ],
                'sigArgs'     => ['score' => $score2 ],
                'reason'      => 'Comparison of score failed: Comparison of scaled failed: value is not the same'
            ],
            [
                'description' => 'extensions only: mismatch',
                'objArgs'     => ['extensions' => $extensions1 ],
                'sigArgs'     => ['extensions' => $extensions2 ],
                'reason'      => 'Comparison of extensions failed: http://id.tincanapi.com/extension/location not in signature'
            ],
            [
                'description' => 'full: success mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['success' => false]),
                'reason'      => 'Comparison of success failed: value is not the same'
            ],
            [
                'description' => 'full: completion mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['completion' => false]),
                'reason'      => 'Comparison of completion failed: value is not the same'
            ],
            [
                'description' => 'full: duration mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['duration' => 'PT150S']),
                'reason'      => 'Comparison of duration failed: value is not the same'
            ],
            [
                'description' => 'full: response mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['response' => 'b']),
                'reason'      => 'Comparison of response failed: value is not the same'
            ],
            [
                'description' => 'full: score mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['score' => $score2]),
                'reason'      => 'Comparison of score failed: Comparison of scaled failed: value is not the same'
            ],
            [
                'description' => 'full: extensions mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['extensions' => $extensions2]),
                'reason'      => 'Comparison of extensions failed: http://id.tincanapi.com/extension/location not in signature'
            ]
        ];
        $this->runSignatureCases("TinCan\Result", $cases);
    }
}
