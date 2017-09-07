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
use TinCan\Group;

class GroupTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    public function testInstantiation() {
        $obj = new Group();
        $this->assertInstanceOf('TinCan\Agent', $obj);
        $this->assertInstanceOf('TinCan\Group', $obj);
        $this->assertAttributeEquals([], 'member', $obj, 'member empty array');
    }

    // TODO: need to loop possible configs
    public function testFromJSONInstantiations() {
        $obj = Group::fromJSON('{"mbox":"' . COMMON_GROUP_MBOX . '", "member":[{"mbox":"' . COMMON_MBOX . '"}]}');
        $this->assertInstanceOf('TinCan\Group', $obj);
        $this->assertSame(COMMON_GROUP_MBOX, $obj->getMbox(), 'mbox value');
        $this->assertEquals([new Agent(['mbox' => COMMON_MBOX])], $obj->getMember(), 'member list');
    }

    // TODO: need to loop versions
    public function testAsVersionMbox() {
        $args      = [
            'mbox' => COMMON_GROUP_MBOX
        ];

        $obj       = Group::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Group';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionMboxSha1() {
        $args      = [
            'mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1
        ];

        $obj       = Group::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Group';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionAccount() {
        $args      = [
            'account' => [
                'name' => COMMON_ACCT_NAME,
                'homePage' => COMMON_ACCT_HOMEPAGE
            ]
        ];

        $obj       = Group::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Group';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionAccountEmptyStrings() {
        $args      = [
            'account' => [
                'name' => '',
                'homePage' => ''
            ]
        ];

        $obj       = Group::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Group';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmptyAccount() {
        $args      = [
            'account' => []
        ];

        $obj       = Group::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Group';
        unset($args['account']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmptyMember() {
        $args      = [
            'member' => []
        ];

        $obj       = Group::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Group';
        unset($args['member']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Group::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $args['objectType'] = 'Group';

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testAddMember() {
        $common_agent = new Agent(['mbox' => COMMON_MBOX]);

        $obj = new Group();

        $obj->addMember([ 'mbox' => COMMON_MBOX ]);
        $this->assertEquals([$common_agent], $obj->getMember(), 'member list create Agent');

        $obj->setMember([]);

        $obj->addMember($common_agent);
        $this->assertEquals([$common_agent], $obj->getMember(), 'member list existing Agent');

        $versioned = $obj->asVersion('1.0.0');
        $this->assertSame($versioned['member'][0], $common_agent->asVersion('1.0.0'));
    }

    public function testCompareWithSignature() {
        $name = 'Test Group Name';
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

        $member1 = new Agent(
            [ 'mbox' => COMMON_MBOX ]
        );
        $member2 = new Agent(
            [ 'account' => $acct1 ]
        );

        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'mbox',
                'objArgs'     => ['mbox' => COMMON_GROUP_MBOX]
            ],
            [
                'description' => 'mbox_sha1sum',
                'objArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1]
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
                'objArgs'     => ['mbox' => COMMON_GROUP_MBOX, 'name' => $name]
            ],
            [
                'description' => 'mbox_sha1sum with name',
                'objArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1, 'name' => $name]
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
                'objArgs'     => ['mbox' => COMMON_GROUP_MBOX, 'name' => $name],
                'sigArgs'     => ['mbox' => COMMON_GROUP_MBOX, 'name' => $name . ' diff']
            ],
            [
                'description' => 'mbox_sha1sum with mismatch name',
                'objArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1, 'name' => $name],
                'sigArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1, 'name' => $name . ' diff']
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
                'objArgs'     => ['mbox' => COMMON_GROUP_MBOX ],
                'sigArgs'     => ['mbox' => 'diff-' . COMMON_GROUP_MBOX ],
                'reason'      => 'Comparison of mbox failed: value is not the same'
            ],
            [
                'description' => 'mbox_sha1sum only: mismatch',
                'objArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1 ],
                'sigArgs'     => ['mbox_sha1sum' => 'diff-' . COMMON_GROUP_MBOX_SHA1 ],
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
                'objArgs'     => ['mbox' => COMMON_GROUP_MBOX],
                'sigArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1]
            ],
            [
                'description' => 'this.mbox_sha1sum to signature.mbox',
                'objArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1],
                'sigArgs'     => ['mbox' => COMMON_GROUP_MBOX]
            ],
            [
                'description' => 'this.mbox to signature.mbox_sha1sum non-matching',
                'objArgs'     => ['mbox' => COMMON_GROUP_MBOX],
                'sigArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1 . '-diff'],
                'reason'      => 'Comparison of this.mbox to signature.mbox_sha1sum failed: no match'
            ],
            [
                'description' => 'this.mbox_sha1sum to signature.mbox non-matching',
                'objArgs'     => ['mbox_sha1sum' => COMMON_GROUP_MBOX_SHA1 . '-diff'],
                'sigArgs'     => ['mbox' => COMMON_GROUP_MBOX],
                'reason'      => 'Comparison of this.mbox_sha1sum to signature.mbox failed: no match'
            ],

            // special cases for unidentified groups, member list needs to match
            [
                'description' => 'anonymous match: empty member list',
                'objArgs' => ['member' => []],
            ],
            [
                'description' => 'anonymous match: single member',
                'objArgs' => ['member' => [$member1]],
            ],
            [
                'description' => 'anonymous match: multiple members',
                'objArgs' => ['member' => [$member1, $member2]],
            ],
            [
                'description' => 'anonymous non-match: sig member missing (empty)',
                'objArgs' => ['member' => [$member1]],
                'sigArgs' => ['member' => []],
                'reason' => 'Comparison of member list failed: array lengths differ'
            ],
            [
                'description' => 'anonymous non-match: this member missing (empty)',
                'objArgs' => ['member' => []],
                'sigArgs' => ['member' => [$member1]],
                'reason' => 'Comparison of member list failed: array lengths differ'
            ],
            [
                'description' => 'anonymous non-match: sig member missing',
                'objArgs' => ['member' => [$member1, $member2]],
                'sigArgs' => ['member' => [$member1]],
                'reason' => 'Comparison of member list failed: array lengths differ'
            ],
            [
                'description' => 'anonymous non-match: this member missing',
                'objArgs' => ['member' => [$member1]],
                'sigArgs' => ['member' => [$member1, $member2]],
                'reason' => 'Comparison of member list failed: array lengths differ'
            ],
            [
                'description' => 'anonymous non-match: different order',
                'objArgs' => ['member' => [$member2, $member1]],
                'sigArgs' => ['member' => [$member1, $member2]],
                'reason' => 'Comparison of member 0 failed: Comparison of mbox failed: value is not the same'
            ]
        ];
        $this->runSignatureCases("TinCan\Group", $cases);
    }
}
