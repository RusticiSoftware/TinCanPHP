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

require_once( 'TinCanApi_Autoloader.php' );

class ActivityTest extenrequire_once( 'TinCanApi_Autoloader.php' );ase {
    public function testInstantiation() {
        $obj = new TinCanAPI_Activity();
        $this->assertInstanceOf('TinCanAPI_Activity', $obj);
        $this->assertAttributeEmpty('id', $obj, 'id empty');
        $this->assertAttributeEmpty('definition', $obj, 'definition empty');
    }

    public function testFromJSONInvalidNull() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_NONE
        );
        $obj = TinCanAPI_Activity::fromJSON(null);
    }

    public function testFromJSONInvalidEmptyString() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_NONE
        );
        $obj = TinCanAPI_Activity::fromJSON('');
    }

    public function testFromJSONInvalidMalformed() {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid JSON: ' . JSON_ERROR_SYNTAX
        );
        $obj = TinCanAPI_Activity::fromJSON('{id:"some value"}');
    }

    public function testFromJSONIDOnly() {
        $obj = TinCanAPI_Activity::fromJSON('{"id":"' . COMMON_ACTIVITY_ID . '"}');
        $this->assertInstanceOf('TinCanAPI_Activity', $obj);
        $this->assertAttributeEquals(COMMON_ACTIVITY_ID, 'id', $obj, 'id matches');
        $this->assertAttributeEmpty('definition', $obj, 'definition empty');
    }

    // TODO: need to loop versions
    public function testAsVersion() {
        $obj = new TinCanAPI_Activity(
            array('id' => COMMON_ACTIVITY_ID)
        );
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals(
            $versioned,
            [ 'objectType' => 'TinCanAPI_Activity', 'id' => COMMON_ACTIVITY_ID ],
            "id only: 1.0.0"
        );
    }
}
