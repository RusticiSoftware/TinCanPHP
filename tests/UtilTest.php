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

use TinCan\Util;

class UtilTest extends \PHPUnit_Framework_TestCase {
    public function testGetUUID() {
        $result = Util::getUUID();

        $this->assertRegExp(Util::UUID_REGEX, $result);
    }

    public function testGetTimestamp() {
        $result = Util::getTimestamp();

        //
        // this isn't intended to match all ISO8601 just *our* format of it, so it should
        // catch regressions, at least more than will be accepted by an LRS which is really
        // ultimately what we want in our tests
        //
        $this->assertRegExp('/\d\d\d\d-[01]\d-[0123]\dT[012]\d:[012345]\d:[012345]\d\.\d\d\d\+00:00/', $result);
    }
}
