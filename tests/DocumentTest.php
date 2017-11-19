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

use TinCan\Document;

class StubDocument extends Document {}

class DocumentTest extends \PHPUnit_Framework_TestCase {
    public function testExceptionOnInvalidDateTime() {
        $this->setExpectedException(
            "InvalidArgumentException",
            'type of arg1 must be string or DateTime'
        );

        $obj = new StubDocument();
        $obj->setTimestamp(1);
    }
}
