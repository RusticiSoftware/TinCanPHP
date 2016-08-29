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

use TinCan\Score;

class ScoreTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    private $emptyProperties = array(
        'scaled',
        'raw',
        'min',
        'max',
    );

    public function testInstantiation() {
        $obj = new Score();
        $this->assertInstanceOf('TinCan\Score', $obj);
        foreach ($this->emptyProperties as $property) {
            $this->assertAttributeEmpty($property, $obj, "$property empty");
        }
    }

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\Score'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\Score'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\Score'));
    }

    public function testScaled() {
        $score = new Score;
        $score->setScaled(0.9);

        $this->assertEquals($score->getScaled(), 0.9);
        $this->assertInternalType('float', $score->getScaled());
    }

    public function testSetScaledBelowMin() {
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('Value must be greater than or equal to %s [-5]', Score::SCALE_MIN)
        );
        $score = new Score;
        $score->setScaled(-5);
    }

    public function testSetScaledAboveMax() {
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('Value must be less than or equal to %s [5]', Score::SCALE_MAX)
        );
        $score = new Score;
        $score->setScaled(5);
    }

    public function testRaw() {
        $score = new Score;
        $score->setRaw(90);

        $this->assertEquals($score->getRaw(), 90);
        $this->assertInternalType('float', $score->getRaw());

        $score = new Score(['min' => 65, 'max' => 85]);
        $score->setRaw(75);
        $this->assertEquals($score->getRaw(), 75, 'between min and max');

        $score = new Score(['min' => 65]);
        $score->setRaw(65);
        $this->assertEquals($score->getRaw(), 65, 'same as min');

        $score = new Score(['max' => 65]);
        $score->setRaw(65);
        $this->assertEquals($score->getRaw(), 65, 'same as max');
    }

    public function testSetRawBelowMin() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Value must be greater than or equal to \'min\' (60) [50]'
        );
        $score = new Score(['min' => 60]);
        $score->setRaw(50);
    }

    public function testSetRawAboveMax() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Value must be less than or equal to \'max\' (90) [95]'
        );
        $score = new Score(['max' => 90]);
        $score->setRaw(95);
    }

    public function testMin() {
        $score = new Score;
        $score->setMin(9);

        $this->assertEquals($score->getMin(), 9);
        $this->assertInternalType('float', $score->getMin());

        $score = new Score(['raw' => 65, 'max' => 85]);
        $score->setMin(35);
        $this->assertEquals($score->getMin(), 35, 'below raw');

        $score = new Score(['raw' => 35, 'max' => 85]);
        $score->setMin(35);
        $this->assertEquals($score->getMin(), 35, 'equal to raw');
    }

    public function testSetMinAboveRaw() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Value must be less than or equal to \'raw\' (50) [60]'
        );
        $score = new Score(['raw' => 50]);
        $score->setMin(60);
    }

    public function testSetMinAboveMax() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Value must be less than \'max\' (90) [95]'
        );
        $score = new Score(['max' => 90]);
        $score->setMin(95);
    }

    public function testMax() {
        $score = new Score;
        $score->setMax(96.3);

        $this->assertEquals($score->getMax(), 96.3);
        $this->assertInternalType('float', $score->getMax());

        $score = new Score(['raw' => 65, 'min' => 35]);
        $score->setMax(85.4);
        $this->assertEquals($score->getMax(), 85.4, 'above raw');

        $score = new Score(['raw' => 35, 'min' => 15]);
        $score->setMax(35);
        $this->assertEquals($score->getMax(), 35, 'equal to raw');
    }

    public function testSetMaxBelowRaw() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Value must be greater than or equal to \'raw\' (60) [50]'
        );
        $score = new Score(['raw' => 60]);
        $score->setMax(50);
    }

    public function testSetMaxBelowMin() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Value must be greater than \'min\' (10) [5]'
        );
        $score = new Score(['min' => 10]);
        $score->setMax(5);
    }

    /**
     * @dataProvider asVersionDataProvider
     */
    public function testAsVersion($args) {
        $obj = new Score($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "version: 1.0.0");
    }

    public function asVersionDataProvider() {
        return [
            'basic'          => [
                [
                    'raw'    => '1.5',
                    'min'    => '1.0',
                    'max'    => '2.0',
                    'scaled' => '.95'
                ]
            ],
            'empty'          => [[]],
            'zero raw'       => [
                [ 'raw' => 0 ]
            ],
            'zero scaled'    => [
                [ 'scaled' => 0 ]
            ],
            'multiple zeros' => [
                [
                    'raw'    => '0',
                    'min'    => '-1.0',
                    'max'    => 2.0,
                    'scaled' => 0
                ]
            ]
        ];
    }

    public function testCompareWithSignature() {
        $full = [
            'raw'    => 97,
            'scaled' => 0.97,
            'min'    => 0,
            'max'    => 100
        ];
        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'raw',
                'objArgs'     => ['raw' => 97]
            ],
            [
                'description' => 'scaled',
                'objArgs'     => ['scaled' => 0.97]
            ],
            [
                'description' => 'min',
                'objArgs'     => ['min' => 60]
            ],
            [
                'description' => 'max',
                'objArgs'     => ['max' => 99]
            ],
            [
                'description' => 'all',
                'objArgs'     => $full
            ],
            [
                'description' => 'raw only: mismatch',
                'objArgs'     => ['raw' => 97 ],
                'sigArgs'     => ['raw' => 87 ],
                'reason'      => 'Comparison of raw failed: value is not the same'
            ],
            [
                'description' => 'scaled only: mismatch',
                'objArgs'     => ['scaled' => 0.97 ],
                'sigArgs'     => ['scaled' => 0.87 ],
                'reason'      => 'Comparison of scaled failed: value is not the same'
            ],
            [
                'description' => 'min only: mismatch',
                'objArgs'     => ['min' => 0 ],
                'sigArgs'     => ['min' => 1 ],
                'reason'      => 'Comparison of min failed: value is not the same'
            ],
            [
                'description' => 'max only: mismatch',
                'objArgs'     => ['max' => 97 ],
                'sigArgs'     => ['max' => 100 ],
                'reason'      => 'Comparison of max failed: value is not the same'
            ],
            [
                'description' => 'full: raw mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['raw' => 79]),
                'reason'      => 'Comparison of raw failed: value is not the same'
            ],
            [
                'description' => 'full: scaled mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['scaled' => 0.96]),
                'reason'      => 'Comparison of scaled failed: value is not the same'
            ],
            [
                'description' => 'full: min mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['min' => 1]),
                'reason'      => 'Comparison of min failed: value is not the same'
            ],
            [
                'description' => 'full: max mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['max' => 98]),
                'reason'      => 'Comparison of max failed: value is not the same'
            ]
        ];
        $this->runSignatureCases("TinCan\Score", $cases);
    }
}
