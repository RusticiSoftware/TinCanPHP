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

use TinCan\Score;

class ScoreTest extends PHPUnit_Framework_TestCase {
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

    public function testSetScaledThrowsException() {
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('Scale must be between %s and %s [5]', Score::SCALE_MIN, Score::SCALE_MAX)
        );
        $score = new Score;
        $score->setScaled(5);
    }

    public function testSetMinThrowsException() {
        $this->setExpectedException('InvalidArgumentException', 'Min must be less than max');
        $score = new Score(['max' => 3.7]);
        $score->setMin(8.1);
    }

    public function testSetMaxThrowsException() {
        $this->setExpectedException('InvalidArgumentException', 'Max must be greater than min');
        $score = new Score(['min' => 5.3, 'max' => 3.7]);
    }

    public function testSetRawThrowsException() {
        $score = new Score(['min' => 1.5, 'max' => 4.3]);
        $this->setExpectedException(
            'InvalidArgumentException',
            'Value must be between 1.5 and 4.3'
        );
        $score->setRaw(1);
    }

    public function testGetRawReturnsFloat() {
        $score = new Score('1.5');
        $this->assertInternalType('float', $score->getRaw());
    }

    public function testGetMinReturnsFloat() {
        $score = new Score(null, '1.5');
        $this->assertInternalType('float', $score->getMin());
    }

    public function testGetMaxReturnsFloat() {
        $score = new Score(null, null, '1.5');
        $this->assertInternalType('float', $score->getMax());
    }

    public function testGetScaledReturnsFloat() {
        $score = new Score(null, null, null, '0.5');
        $this->assertInternalType('float', $score->getScaled());
    }

    public function testGetValueWithoutRawReturnsNull() {
        $score = new Score;
        $this->assertNull($score->getValue());
    }

    public function testGetValueWithoutScaledReturnsRoundedRaw() {
        $raw   = 3.92013;
        $score = new Score($raw);
        $this->assertEquals(
            round($raw, Score::DEFAULT_PRECISION),
            $score->getValue()
        );
    }

    public function testGetValueWithScaledReturnsScaledAndRoundedRaw() {
        $raw    = 3.92013;
        $scaled = 0.8;
        $score  = new Score($raw, null, null, $scaled);
        $this->assertEquals(
            round($raw * $scaled, Score::DEFAULT_PRECISION),
            $score->getValue()
        );
    }

    public function testAsVersion() {
        $args = [
            'raw'    => '1.5',
            'min'    => '1.0',
            'max'    => '2.0',
            'scaled' => '.95'
        ];
        $obj       = new Score($args);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "version: 1.0.0");
    }
}
