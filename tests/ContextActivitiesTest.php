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

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\ContextActivities'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\ContextActivities'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\ContextActivities'));
    }

    public function testFromJSONInstantiations() {
        $common_activity = new TinCan\Activity(self::$common_activity_cfg);

        $all_json = array();
        foreach (self::$listProps as $k) {
            $getMethod = 'get' . ucfirst($k);

            $prop_json = '"' . $k . '":[' . json_encode($common_activity->asVersion('1.0.0')) . ']';

            array_push($all_json, $prop_json);

            $obj = ContextActivities::fromJSON('{' . $prop_json . '}');

            $this->assertInstanceOf('TinCan\ContextActivities', $obj);
            $this->assertEquals([$common_activity], $obj->$getMethod(), "$k list");
        }

        $obj = ContextActivities::fromJSON('{' . join(",", $all_json) . "}");

        $this->assertInstanceOf('TinCan\ContextActivities', $obj);
        $this->assertEquals([$common_activity], $obj->getCategory(), "all props: category list");
        $this->assertEquals([$common_activity], $obj->getParent(), "all props: parent list");
        $this->assertEquals([$common_activity], $obj->getGrouping(), "all props: grouping list");
        $this->assertEquals([$common_activity], $obj->getOther(), "all props: other list");
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $obj = new ContextActivities();
        $obj->setCategory(self::$common_activity_cfg);
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals(
            ['category' => [
                ['objectType' => 'Activity', 'id' => COMMON_ACTIVITY_ID]
            ]],
            $versioned,
            "category only: 1.0.0"
        );
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
