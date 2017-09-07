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

use TinCan\Person;

class PersonTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    public function testInstantiation() {
        $obj = new Person();
        $this->assertInstanceOf('TinCan\Person', $obj);
        $this->assertAttributeEmpty('name', $obj, 'name empty');
        $this->assertAttributeEmpty('mbox', $obj, 'mbox empty');
        $this->assertAttributeEmpty('mbox_sha1sum', $obj, 'mbox_sha1sum empty');
        $this->assertAttributeEmpty('openid', $obj, 'openid empty');
        $this->assertAttributeEmpty('account', $obj, 'account empty');
    }

    public function testFromJSONInvalidNull() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Person::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Person::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException('TinCan\JSONParseErrorException');
        $obj = Person::fromJSON('{name:"some value"}');
    }

    // TODO: need to loop possible configs
    public function testFromJSONInstantiations() {
        $obj = Person::fromJSON('{"mbox":["' . COMMON_MBOX . '","'.COMMON_MBOX.'"]}');
        $this->assertInstanceOf('TinCan\Person', $obj);
        $this->assertSame(array(COMMON_MBOX, COMMON_MBOX), $obj->getMbox(), 'mbox value');
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $obj = new Person(
            [
                'mbox' => array(COMMON_MBOX),
                'account' => array(
                    array(
                        'name' => COMMON_ACCT_NAME,
                        'homePage' => COMMON_ACCT_HOMEPAGE
                    )
                )
            ]
        );
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals(
            [
                'objectType' => 'Person',
                'mbox' => array(COMMON_MBOX),
                'account' => array(
                    array(
                        'name' => COMMON_ACCT_NAME,
                        'homePage' => COMMON_ACCT_HOMEPAGE
                    )
                )
            ],
            $versioned,
            "mbox only: 1.0.0"
        );
    }

    public function testSetMbox() {
        $obj = new Person();

        $obj->setMbox(array(COMMON_MBOX));
        $this->assertSame(array(COMMON_MBOX), $obj->getMbox());

        //
        // make sure it doesn't add mailto when null
        //
        $obj->setMbox(null);
        $this->assertAttributeEmpty('mbox', $obj);
    }

    public function testGetMbox_sha1sum() {
        $obj = new Person(['mbox_sha1sum' => array(COMMON_MBOX_SHA1)]);
        $this->assertSame($obj->getMbox_sha1sum(), array(COMMON_MBOX_SHA1), 'original sha1');
    }
}
