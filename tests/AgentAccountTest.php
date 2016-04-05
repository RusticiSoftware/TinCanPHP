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

use TinCan\AgentAccount;

class AgentAccountTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    const HOMEPAGE = 'http://tincanapi.com';
    const NAME = 'test';

    public function testInstantiation() {
        $obj = new AgentAccount();
        $this->assertInstanceOf('TinCan\AgentAccount', $obj);
        $this->assertAttributeEmpty('homePage', $obj, 'homePage empty');
        $this->assertAttributeEmpty('name', $obj, 'name empty');
    }

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\AgentAccount'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\AgentAccount'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\AgentAccount'));
    }

    public function testName() {
        $obj  = new AgentAccount();
        $name = COMMON_ACCT_NAME;
        $this->assertSame($obj, $obj->setName($name));
        $this->assertSame($name, $obj->getName());
    }

    public function testHomePage() {
        $obj      = new AgentAccount();
        $homePage = COMMON_ACCT_HOMEPAGE;
        $this->assertSame($obj, $obj->setHomePage($homePage));
        $this->assertSame($homePage, $obj->getHomePage());
    }

    public function testAsVersion() {
        $args      = [
            'name' => COMMON_ACCT_NAME,
            'homePage' => COMMON_ACCT_HOMEPAGE
        ];

        $obj       = AgentAccount::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = AgentAccount::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmptyStrings() {
        $args      = [
            'name' => '',
            'homePage' => ''
        ];

        $obj       = AgentAccount::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testCompareWithSignature() {
        $full = [
            'homePage' => COMMON_ACCT_HOMEPAGE,
            'name'     => COMMON_ACCT_NAME
        ];
        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'homePage',
                'objArgs'     => ['homePage' => COMMON_ACCT_HOMEPAGE]
            ],
            [
                'description' => 'name',
                'objArgs'     => ['name' => COMMON_ACCT_NAME]
            ],
            [
                'description' => 'all',
                'objArgs'     => $full
            ],
            [
                'description' => 'homepage only: mismatch',
                'objArgs'     => ['homePage' => COMMON_ACCT_HOMEPAGE],
                'sigArgs'     => ['homePage' => COMMON_ACCT_HOMEPAGE . '/invalid'],
                'reason'      => 'Comparison of homePage failed: value is not the same'
            ],
            [
                'description' => 'name only: mismatch',
                'objArgs'     => ['name' => COMMON_ACCT_NAME],
                'sigArgs'     => ['name' => 'diff'],
                'reason'      => 'Comparison of name failed: value is not the same'
            ],
            [
                'description' => 'full: homePage mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['homePage' => COMMON_ACCT_HOMEPAGE . '/invalid']),
                'reason'      => 'Comparison of homePage failed: value is not the same'
            ],
            [
                'description' => 'full: name mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['name' => 'diff']),
                'reason'      => 'Comparison of name failed: value is not the same'
            ],
            [
                'description' => 'full: both mismatch',
                'objArgs'     => $full,
                'sigArgs'     => ['homePage' => COMMON_ACCT_HOMEPAGE . '/invalid', 'name' => 'diff'],
                'reason'      => 'Comparison of name failed: value is not the same'
            ]
        ];
        $this->runSignatureCases("TinCan\AgentAccount", $cases);
    }
}
