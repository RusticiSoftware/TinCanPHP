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

class UtilTest extends require_once( 'TinCanApi_Autoloader.php' ); {
    public function testGetUUID() {
        $result = TinCanAPI_Util::getUUID();

        $this->assertRegExp(TinCanAPI_Util::UUID_REGEX, $result);
    }
}
