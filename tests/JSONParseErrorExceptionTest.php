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

use TinCan\JSONParseErrorException;

class JSONParseErrorExceptionTest extends PHPUnit_Framework_TestCase
{
    private $exception;
    private $malformedValue  = '.....';
    private $jsonErrorNumber;
    private $jsonErrorMessage;

    public function setUp() {
        $this->jsonErrorNumber = JSON_ERROR_SYNTAX;
        $this->jsonErrorMessage = 'Syntax error, malformed JSON';
        $this->exception = new JSONParseErrorException(
            $this->malformedValue,
            $this->jsonErrorNumber,
            $this->jsonErrorMessage
        );
    }

    public function testFetchErrorInformation() {
        $this->assertEquals($this->malformedValue, $this->exception->malformedValue());
        $this->assertEquals($this->jsonErrorNumber, $this->exception->jsonErrorNumber());
        $this->assertEquals($this->jsonErrorMessage, $this->exception->jsonErrorMessage());
    }

    public function testGetMessage() {
        $format = 'Invalid JSON "%s": %s (%d)';
        $expected = sprintf($format, $this->malformedValue, $this->jsonErrorMessage, $this->jsonErrorNumber);
        $this->assertEquals($expected, $this->exception->getMessage());
    }
}
