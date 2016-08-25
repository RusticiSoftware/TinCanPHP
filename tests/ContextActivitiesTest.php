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

use TinCan\Activity;
use TinCan\ContextActivities;

class ContextActivitiesTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

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
        $common_activity = new Activity(self::$common_activity_cfg);

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
    public function testAsVersionWithSingleList() {
        $keys = ['category', 'parent', 'grouping', 'other'];
        foreach ($keys as $k) {
            $args      = [];
            $args[$k]  = [ self::$common_activity_cfg ];

            $obj       = ContextActivities::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
            $versioned = $obj->asVersion('1.0.0');

            $args[$k][0]['objectType'] = 'Activity';

            $this->assertEquals($versioned, $args, "serialized version matches original");

            unset($args[$k][0]['objectType']);
        }
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = ContextActivities::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionWithEmptyList() {
        $keys = ['category', 'parent', 'grouping', 'other'];
        foreach ($keys as $k) {
            $args      = [];
            $args[$k]  = [];

            $obj       = ContextActivities::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
            $versioned = $obj->asVersion('1.0.0');

            unset($args[$k]);

            $this->assertEquals($versioned, $args, "serialized version matches corrected");
        }
    }

    public function testListSetters() {
        $common_activity = new Activity(self::$common_activity_cfg);

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

    public function testCompareWithSignature() {
        $acts = [
            new Activity(
                ['id' => COMMON_ACTIVITY_ID . '/0']
            ),
            new Activity(
                ['id' => COMMON_ACTIVITY_ID . '/1']
            ),
            new Activity(
                ['id' => COMMON_ACTIVITY_ID . '/2']
            ),
            new Activity(
                ['id' => COMMON_ACTIVITY_ID . '/3']
            )
        ];

        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ]
        ];
        foreach (self::$listProps as $k) {
            array_push(
                $cases,
                [
                    'description' => "$k single",
                    'objArgs'     => [$k => [ $acts[0] ]],
                ],
                [
                    'description' => "$k multiple",
                    'objArgs'     => [$k => [ $acts[0], $acts[1] ]],
                ],
                [
                    'description' => "$k single empty sig (no $k set)",
                    'objArgs'     => [$k => [ $acts[0] ]],
                    'sigArgs'     => [],
                    'reason'      => "Comparison of $k failed: array lengths differ"
                ],
                [
                    'description' => "$k single empty sig",
                    'objArgs'     => [$k => [ $acts[0] ]],
                    'sigArgs'     => [$k => []],
                    'reason'      => "Comparison of $k failed: array lengths differ"
                ],
                [
                    'description' => "$k multiple single sig",
                    'objArgs'     => [$k => [ $acts[0], $acts[1] ]],
                    'sigArgs'     => [$k => [ $acts[1] ]],
                    'reason'      => "Comparison of $k failed: array lengths differ"
                ],
                [
                    'description' => "$k single multiple sig",
                    'objArgs'     => [$k => [ $acts[0] ]],
                    'sigArgs'     => [$k => [ $acts[0], $acts[1] ]],
                    'reason'      => "Comparison of $k failed: array lengths differ"
                ],
                [
                    'description' => "$k single diff sig",
                    'objArgs'     => [$k => [ $acts[0] ]],
                    'sigArgs'     => [$k => [ $acts[1] ]],
                    'reason'      => 'Comparison of ' . $k . '[0] failed: Comparison of id failed: value is not the same'
                ],
                [
                    'description' => "$k multiple diff order",
                    'objArgs'     => [$k => [ $acts[0], $acts[1] ]],
                    'sigArgs'     => [$k => [ $acts[1], $acts[0] ]],
                    'reason'      => 'Comparison of ' . $k . '[0] failed: Comparison of id failed: value is not the same'
                ]
            );
        }

        foreach ([
            ['category', 'parent'],
            ['category', 'other'],
            ['category', 'grouping'],
            ['parent', 'other'],
            ['parent', 'grouping'],
            ['grouping', 'other'],
            ['category', 'parent', 'other'],
            ['category', 'parent', 'grouping'],
            ['category', 'other', 'grouping'],
            ['parent', 'other', 'grouping'],
            self::$listProps
        ] as $set) {
            $prefix = implode(', ', $set);
            $new_cases = [
                [
                    'description' => $prefix,
                    'objArgs' => [],
                    'sigArgs' => [],
                ],
                [
                    'description' => "$prefix: empty sig",
                    'objArgs' => [],
                    'sigArgs' => [],
                    'reason' => 'Comparison of ' . $set[0] . ' failed: array lengths differ'
                ],
                [
                    'description' => "$prefix: one missing this",
                    'objArgs' => [],
                    'sigArgs' => [],
                    'reason' => 'Comparison of ' . $set[0] . ' failed: array lengths differ'
                ],
                [
                    'description' => "$prefix: one missing signature",
                    'objArgs' => [],
                    'sigArgs' => [],
                    'reason' => 'Comparison of ' . $set[0] . ' failed: array lengths differ'
                ]
            ];

            for ($i = 0; $i < count($set); $i++) {
                $new_cases[0]['objArgs'][ $set[$i] ] = $acts[$i];
                $new_cases[0]['sigArgs'][ $set[$i] ] = $acts[$i];

                $new_cases[1]['objArgs'][ $set[$i] ] = $acts[$i];

                $new_cases[2]['objArgs'][ $set[$i] ] = $acts[$i];
                $new_cases[3]['objArgs'][ $set[$i] ] = $acts[$i];
                if ($i !== 0) {
                    $new_cases[2]['sigArgs'][ $set[$i] ] = $acts[$i];
                    $new_cases[3]['sigArgs'][ $set[$i] ] = $acts[$i];
                }
            }

            $cases = array_merge($cases, $new_cases);
        }
        $this->runSignatureCases("TinCan\ContextActivities", $cases);
    }

    /**
     * @dataProvider invalidListSetterDataProvider
     */
    public function testListSetterThrowsInvalidArgumentException($publicMethodName, $invalidValue) {
        $this->setExpectedException(
            'InvalidArgumentException',
            'type of arg1 must be Activity, array of Activity properties, or array of Activity/array of Activity properties'
        );
        $obj = new ContextActivities();
        $obj->$publicMethodName($invalidValue);
    }

    public function invalidListSetterDataProvider() {
        $invalidValue = 1;
        return [
            ["setCategory", $invalidValue],
            ["setParent", $invalidValue],
            ["setGrouping", $invalidValue],
            ["setOther", $invalidValue]
        ];
    }
}
