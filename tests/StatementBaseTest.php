<?php
/*
    Copyright 2016 Rustici Software

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

use TinCan\StatementBase;
use TinCan\SubStatement;
use TinCan\Verb;
use TinCan\Agent;
use TinCan\Context;
use TinCan\Result;

class StubStatementBase extends StatementBase {}

class StatementBaseTest extends \PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $obj = new StubStatementBase();
    }

    public function testSetTargetAsSAgent() {
        $obj = new StubStatementBase();
        $ss = [
            'objectType' => 'Agent'
        ];
        $obj->setTarget($ss);
        $this->assertInstanceOf('TinCan\Agent', $obj->getTarget());
    }

    public function testSetTargetAsGroup() {
        $obj = new StubStatementBase();
        $ss = [
            'objectType' => 'Group'
        ];
        $obj->setTarget($ss);
        $this->assertInstanceOf('TinCan\Group', $obj->getTarget());
    }

    public function testSetTargetAsSubStatement() {
        $obj = new StubStatementBase();
        $ss = [
            'objectType' => 'SubStatement'
        ];
        $obj->setTarget($ss);
        $this->assertInstanceOf('TinCan\SubStatement', $obj->getTarget());
    }

    public function testSetTargetInvalidArgumentException() {
        $badObjectType = 'imABadObjectType';
        $this->setExpectedException(
            "InvalidArgumentException",
            "arg1 must implement the StatementTargetInterface objectType not recognized:$badObjectType"
        );
        $obj = new StubStatementBase();
        $ss = [
            'objectType' => $badObjectType
        ];
        $obj->setTarget($ss);
    }

    public function testSetActorAsGroup() {
        $obj = new StubStatementBase();
        $ss = [
            'objectType' => 'Group'
        ];
        $obj->setActor($ss);
        $this->assertInstanceOf('TinCan\Group', $obj->getActor());
    }

    public function testSetTimestampInvalidArgumentException() {
        $this->setExpectedException(
            "InvalidArgumentException",
            'type of arg1 must be string or DateTime'
        );

        $obj = new StubStatementBase();
        $obj->setTimestamp(1);
    }

    /**
     * @dataProvider statementPropertyValueProvider
     */
    public function testCompareWithSignaturePropertyMissing($property, $value) {
        $signature = new \stdClass;
        $setMethodName = 'set' . ucfirst($property);

        $obj = new StubStatementBase();
        $obj->$setMethodName($value);

        $result = $obj->compareWithSignature($signature);
        $this->assertFalse($result['success']);
        $this->assertEquals("Comparison of $property failed: value not in signature", $result['reason']);

        $obj = new StubStatementBase();
        $signature->$property = $value;

        $result = $obj->compareWithSignature($signature);
        $this->assertFalse($result['success']);
        $this->assertEquals("Comparison of $property failed: value not in this", $result['reason']);
    }

    public function statementPropertyValueProvider() {
        return [
            ['actor',   new Agent()],
            ['verb',    new Verb()],
            ['target',  new Agent()],
            ['context', new Context()],
            ['result',  new Result()],
        ];
    }

    public function testCompareWithSignatureTimestampMissing() {
        $timestamp = "2004-02-12T15:19:21+00:00";
        $signature = new \stdClass;

        $obj = new StubStatementBase();
        $obj->setTimestamp($timestamp);

        $result = $obj->compareWithSignature($signature);

        $this->assertFalse($result['success']);
        $this->assertEquals("Comparison of timestamp failed: value not in signature", $result['reason']);

        $obj = new StubStatementBase();
        $signature->timestamp = $timestamp;

        $result = $obj->compareWithSignature($signature);

        $this->assertFalse($result['success']);
        $this->assertEquals("Comparison of timestamp failed: value not in this", $result['reason']);
    }

    public function testCompareWithSignatureTimestampNotEqual() {
        $timestamp = "2004-02-12T15:19:21+00:00";
        $signature = new \stdClass;

        $obj = new StubStatementBase();
        $obj->setTimestamp($timestamp);
        $signature->timestamp = "2005-02-12T15:19:21+00:00";

        $result = $obj->compareWithSignature($signature);

        $this->assertFalse($result['success']);
        $this->assertEquals("Comparison of timestamp failed: value is not the same", $result['reason']);

        //Now check with microseconds.
        $timestamp = "2012-07-08 11:14:15.638276";
        $obj = new StubStatementBase();
        $obj->setTimestamp($timestamp);
        $signature->timestamp = "2012-07-08 11:14:15.638286";

        $dt = new \DateTime($timestamp);
        $dt2 = new \DateTime($signature->timestamp);

        $result = $obj->compareWithSignature($signature);

        $this->assertFalse($result['success']);
        $this->assertEquals("Comparison of timestamp failed: value is not the same", $result['reason']);
    }
}
