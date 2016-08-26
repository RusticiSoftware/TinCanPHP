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

class SignatureComparisonStub {
    use \TinCan\SignatureComparisonTrait;

    public static function runDoMatch($a, $b, $description) {
        return self::doMatch($a, $b, $description);
    }
}

class SignatureComparisonTraitTest extends \PHPUnit_Framework_TestCase {
    public function testDoMatch() {
        $description = "A test Description";

        $a = new \stdClass;
        $result = SignatureComparisonStub::runDoMatch($a, false, $description);

        $this->assertFalse($result['success']);
        $this->assertEquals("Comparison of $description failed: not a " . get_class($a) . " value", $result['reason']);

        $result = SignatureComparisonStub::runDoMatch([], false, $description);

        $this->assertFalse($result['success']);
        $this->assertEquals("Comparison of $description failed: not an array in signature", $result['reason']);
    }
}
