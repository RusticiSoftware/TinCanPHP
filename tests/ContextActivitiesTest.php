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

use TinCan\ContextActivities;

class ContextActivitiesTest extends PHPUnit_Framework_TestCase {
    static private $listProps = ['category', 'parent', 'grouping', 'other'];
    static private $common_activity_cfg = [
        'id' => COMMON_ACTIVITY_ID
    ];

    public function testInstantiation() {
        $obj = new ContextActivities();
        $this->assertInstanceOf('TinCan\ContextActivities', $obj);
        foreach (self::$listProps as $k) {
            $this->assertAttributeEquals([], $k, $obj, "$k empty array");
        }
    }

    /*
    // TODO: need to loop possible configs
    public function testFromJSONInstantiations() {
        $obj = ContextActivities::fromJSON('{"mbox":"' . COMMON_GROUP_MBOX . '", "member":[{"mbox":"' . COMMON_MBOX . '"}]}');
        $this->assertInstanceOf('TinCan\ContextActivities', $obj);
        $this->assertSame(COMMON_GROUP_MBOX, $obj->getMbox(), 'mbox value');
        $this->assertEquals([['mbox' => COMMON_MBOX]], $obj->getMember(), 'member list');
    }
    */

    // TODO: need to loop versions
    public function testAsVersion() {
        $obj = new ContextActivities();
        $versioned = $obj->asVersion('1.0.0');

        //$this->assertEquals(
            //[ 'objectType' => 'ContextActivities' ],
            //$versioned,
            //"empty: 1.0.0"
        //);
    }

    public function testListSetters() {
        $common_activity = new TinCan\Activity(self::$common_activity_cfg);

        foreach (self::$listProps as $k) {
            $setMethod = 'set' . ucfirst($k);
            $getMethod = 'get' . ucfirst($k);

            $obj = new ContextActivities();

            $obj->$setMethod($common_activity);
            $this->assertEquals([$common_activity], $obj->$getMethod(), "$k: single Activity");

            $obj->$setMethod([]);
            $this->assertEquals([], $obj->$getMethod(), "$k: empty array");

            $obj->$setMethod([$common_activity]);
            $this->assertEquals([$common_activity], $obj->$getMethod(), "$k: array of single Activity");

            $obj->$setMethod([]);

            $obj->$setMethod(self::$common_activity_cfg);
            $this->assertEquals([$common_activity], $obj->$getMethod(), "$k: single Activity configuration");

            $obj->$setMethod([]);

            $obj->$setMethod([self::$common_activity_cfg]);
            $this->assertEquals([$common_activity], $obj->$getMethod(), "$k: array of single Activity configuration");
        }
    }
}
