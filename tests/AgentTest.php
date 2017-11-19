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

use TinCan\Agent;
use TinCan\AgentAccount;

class AgentTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    public function testInstantiation() {
        $obj = new Agent();
        $this->assertInstanceOf('TinCan\Agent', $obj);
        $this->assertAttributeEmpty('name', $obj, 'name empty');
        $this->assertAttributeEmpty('mbox', $obj, 'mbox empty');
        $this->assertAttributeEmpty('mbox_sha1sum', $obj, 'mbox_sha1sum empty');
        $this->assertAttributeEmpty('openid', $obj, 'openid empty');
        $this->assertAttributeEmpty('account', $obj, 'account empty');
    }

    public function testFromJSONInvalidNull() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Agent::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Agent::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Agent::fromJSON('{name:"some value"}');
    }

    // TODO: need to loop possible configs
    public function testFromJSONInstantiations() {
        $obj = Agent::fromJSON('{"mbox":"' . COMMON_MBOX . '"}');
        $this->assertInstanceOf('TinCan\Agent', $obj);
        $this->assertSame(COMMON_MBOX, $obj->getMbox(), 'mbox value');
    }

    // TODO: need to loop versions
    public function testAsVersionMbox() {
        $args      = [
            'mbox' => COMMON_MBOX
        ];

        $obj       = Agent::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Agent';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionMboxSha1() {
        $args      = [
            'mbox_sha1sum' => COMMON_MBOX_SHA1
        ];

        $obj       = Agent::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Agent';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionAccount() {
        $args      = [
            'account' => [
                'name' => COMMON_ACCT_NAME,
                'homePage' => COMMON_ACCT_HOMEPAGE
            ]
        ];

        $obj       = Agent::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Agent';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionAccountEmptyStrings() {
        $args      = [
            'account' => [
                'name' => '',
                'homePage' => ''
            ]
        ];

        $obj       = Agent::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Agent';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmptyAccount() {
        $args      = [
            'account' => []
        ];

        $obj       = Agent::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Agent';
        unset($args['account']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Agent::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Agent';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testIsIdentified() {
        $identified = [
            [
                'description' => 'mbox',
                'args' => ['mbox' => COMMON_MBOX]
            ],
            [
                'description' => 'mbox_sha1sum',
                'args' => ['mbox_sha1sum' => COMMON_MBOX_SHA1]
            ],
            [
                'description' => 'openid',
                'args' => ['openid' => COMMON_OPENID]
            ],
            [
                'description' => 'account',
                'args' => ['account' => ['homePage' => COMMON_ACCT_HOMEPAGE, 'name' => COMMON_ACCT_NAME]]
            ]
        ];
        foreach ($identified as $case) {
            $obj = new Agent ($case['args']);
            $this->assertTrue($obj->isIdentified(), 'identified ' . $case['description']);
        }

        $notIdentified = [
            [
                'description' => 'empty',
                'args' => []
            ],
            [
                'description' => 'name only',
                'args' => ['name' => 'Test']
            ]
        ];
        foreach ($notIdentified as $case) {
            $obj = new Agent ($case['args']);
            $this->assertFalse($obj->isIdentified(), 'not identified ' . $case['description']);
        }
    }

    public function testSetMbox() {
        $obj = new Agent();

        $obj->setMbox(COMMON_MBOX);
        $this->assertSame(COMMON_MBOX, $obj->getMbox());

        $obj->setMbox(COMMON_EMAIL);
        $this->assertSame(COMMON_MBOX, $obj->getMbox());

        //
        // make sure it doesn't add mailto when null
        //
        $obj->setMbox(null);
        $this->assertAttributeEmpty('mbox', $obj);
    }

    public function testGetMbox_sha1sum() {
        $obj = new Agent(['mbox_sha1sum' => COMMON_MBOX_SHA1]);
        $this->assertSame($obj->getMbox_sha1sum(), COMMON_MBOX_SHA1, 'original sha1');

        $obj = new Agent(['mbox' => COMMON_MBOX]);
        $this->assertSame($obj->getMbox_sha1sum(), COMMON_MBOX_SHA1, 'sha1 from mbox');
    }

    public function testCompareWithSignature() {
        $name = 'Test Name';
        $acct1 = new AgentAccount(
            [
                'homePage' => COMMON_ACCT_HOMEPAGE,
                'name'     => COMMON_ACCT_NAME
            ]
        );
        $acct2 = new AgentAccount(
            [
                'homePage' => COMMON_ACCT_HOMEPAGE,
                'name'     => COMMON_ACCT_NAME . '-diff'
            ]
        );

        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'mbox',
                'objArgs'     => ['mbox' => COMMON_MBOX]
            ],
            [
                'description' => 'mbox_sha1sum',
                'objArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1]
            ],
            [
                'description' => 'openid',
                'objArgs'     => ['openid' => COMMON_OPENID]
            ],
            [
                'description' => 'account',
                'objArgs'     => ['account' => $acct1]
            ],
            [
                'description' => 'mbox with name',
                'objArgs'     => ['mbox' => COMMON_MBOX, 'name' => $name]
            ],
            [
                'description' => 'mbox_sha1sum with name',
                'objArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1, 'name' => $name]
            ],
            [
                'description' => 'openid with name',
                'objArgs'     => ['openid' => COMMON_OPENID, 'name' => $name]
            ],
            [
                'description' => 'account with name',
                'objArgs'     => ['account' => $acct1, 'name' => $name]
            ],
            [
                'description' => 'mbox with mismatch name',
                'objArgs'     => ['mbox' => COMMON_MBOX, 'name' => $name],
                'sigArgs'     => ['mbox' => COMMON_MBOX, 'name' => $name . ' diff']
            ],
            [
                'description' => 'mbox_sha1sum with mismatch name',
                'objArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1, 'name' => $name],
                'sigArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1, 'name' => $name . ' diff']
            ],
            [
                'description' => 'openid with mismatch name',
                'objArgs'     => ['openid' => COMMON_OPENID, 'name' => $name],
                'sigArgs'     => ['openid' => COMMON_OPENID, 'name' => $name . ' diff']
            ],
            [
                'description' => 'account with mismatch name',
                'objArgs'     => ['account' => $acct1, 'name' => $name],
                'sigArgs'     => ['account' => $acct1, 'name' => $name . ' diff']
            ],
            [
                'description' => 'mbox only: mismatch',
                'objArgs'     => ['mbox' => COMMON_MBOX ],
                'sigArgs'     => ['mbox' => 'diff-' . COMMON_MBOX ],
                'reason'      => 'Comparison of mbox failed: value is not the same'
            ],
            [
                'description' => 'mbox_sha1sum only: mismatch',
                'objArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1 ],
                'sigArgs'     => ['mbox_sha1sum' => 'diff-' . COMMON_MBOX_SHA1 ],
                'reason'      => 'Comparison of mbox_sha1sum failed: value is not the same'
            ],
            [
                'description' => 'openid only: mismatch',
                'objArgs'     => ['openid' => COMMON_OPENID ],
                'sigArgs'     => ['openid' => COMMON_OPENID . 'diff/' ],
                'reason'      => 'Comparison of openid failed: value is not the same'
            ],
            [
                'description' => 'account only: mismatch',
                'objArgs'     => ['account' => $acct1 ],
                'sigArgs'     => ['account' => $acct2 ],
                'reason'      => 'Comparison of account failed: Comparison of name failed: value is not the same'
            ],

            //
            // special cases where we can try to equate an mbox and an mbox SHA1 sum
            //
            [
                'description' => 'this.mbox to signature.mbox_sha1sum',
                'objArgs'     => ['mbox' => COMMON_MBOX],
                'sigArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1]
            ],
            [
                'description' => 'this.mbox_sha1sum to signature.mbox',
                'objArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1],
                'sigArgs'     => ['mbox' => COMMON_MBOX]
            ],
            [
                'description' => 'this.mbox to signature.mbox_sha1sum non-matching',
                'objArgs'     => ['mbox' => COMMON_MBOX],
                'sigArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1 . '-diff'],
                'reason'      => 'Comparison of this.mbox to signature.mbox_sha1sum failed: no match'
            ],
            [
                'description' => 'this.mbox_sha1sum to signature.mbox non-matching',
                'objArgs'     => ['mbox_sha1sum' => COMMON_MBOX_SHA1 . '-diff'],
                'sigArgs'     => ['mbox' => COMMON_MBOX],
                'reason'      => 'Comparison of this.mbox_sha1sum to signature.mbox failed: no match'
            ],
        ];
        $this->runSignatureCases("TinCan\Agent", $cases);
    }
}
