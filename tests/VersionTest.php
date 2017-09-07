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

use TinCan\Version;

class VersionTest extends \PHPUnit_Framework_TestCase {
    public function testStaticFactoryReturnsInstance() {
        $this->assertInstanceOf("TinCan\Version", Version::v101(), "factory returns instance");
    }

    public function testToString() {
        $this->assertInternalType("string", (string) Version::v101(), "object converts to string");
    }

    public function testHasValueReturnsBool() {
        $this->assertTrue(Version::v101()->hasValue(Version::V101), "object has correct value");
    }

    public function testHasAnyValueReturnsBool() {
        $this->assertFalse(Version::v101()->hasAnyValue([Version::V100, Version::V095]), "object does not have values");
    }

    public function testIsSupportedReturnsBool() {
        $this->assertTrue(Version::v100()->isSupported(), "1.0.0 should be supported");
        $this->assertFalse(Version::v095()->isSupported(), "0.95 should not be supported");
    }

    public function testIsLatestReturnsBool() {
        $this->assertTrue(Version::v101()->isLatest(), "1.0.1 should be the latest version");
        $this->assertFalse(Version::v095()->isLatest(), "0.95 should not be the latest version");
    }

    public function testSupported() {
        $result = Version::supported();
        $this->assertNotContains(Version::V095, $result, "0.95 not included");
    }

    public function testLatest() {
        $this->assertSame(Version::V101, Version::latest(), "match latest");
    }

    public function testVersionFromString() {
        $number = '1.0.1';
        $version = Version::fromString($number);

        $this->assertTrue($version->hasValue($number));
    }

    public function testInvalidArgumentExceptionIsThrown() {
        $number = '1.8.01';
        $this->setExpectedException(
            'InvalidArgumentException',
            "Invalid version [$number]"
        );
        $version = Version::fromString($number);
    }
}
