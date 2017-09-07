<?php
/*
    Copyright 2015 Rustici Software

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

use TinCan\LanguageMap;

class LanguageMapTest extends \PHPUnit_Framework_TestCase {
    const NAME = 'testName';

    public function testInstantiation() {
        $obj = new LanguageMap();
        $this->assertInstanceOf('TinCan\LanguageMap', $obj);
    }

    public function testAsVersion() {
        $args      = ['en' => [self::NAME]];

        $obj       = LanguageMap::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion();

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = LanguageMap::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, null, "serialization returns null");
    }

    public function testAsVersionValueEmptyString() {
        $args      = ['en' => ['']];

        $obj       = LanguageMap::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion();

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testGetNegotiatedLanguageString() {
        $langs = [
            'en-GB' => 'petrol',
            'en-US' => 'gasoline'
        ];
        $obj = new LanguageMap($langs);

        $usValue = $obj->getNegotiatedLanguageString('en-US;q=0.8, en-GB;q=0.6');
        $ukValue = $obj->getNegotiatedLanguageString('en-GB;q=0.8, en-US;q=0.6');

        $this->assertEquals($usValue, $langs['en-US'], 'US name equal');
        $this->assertEquals($ukValue, $langs['en-GB'], 'UK name equal');

        $nullValue = $obj->getNegotiatedLanguageString();
        $this->assertEquals($nullValue, $langs['en-GB'], 'from null: UK name equal');

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $restore = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';

        $nullAcceptValue = $obj->getNegotiatedLanguageString();
        $this->assertEquals($nullAcceptValue, $langs['en-US'], 'from server: US name equal');

        if (isset($restore)) {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $restore;
        }
        else {
            unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        $langs = [
            'en-US' => 'gasoline'
        ];
        $obj = new LanguageMap($langs);

        $this->assertEquals($obj->getNegotiatedLanguageString('en'), $langs['en-US'], 'from prefix');

        $langs = [
            'fr-FR' => 'essence'
        ];
        $obj = new LanguageMap($langs);

        $this->assertEquals($obj->getNegotiatedLanguageString('en, *'), $langs['fr-FR'], 'no matched');

        $langs = [
            'fr-FR' => 'essence',
            'und' => 'fuel',
        ];
        $obj = new LanguageMap($langs);

        $this->assertEquals($obj->getNegotiatedLanguageString('en'), $langs['und'], 'no matched with und');
    }
}
