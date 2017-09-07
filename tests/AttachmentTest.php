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

use TinCan\Attachment;
use TinCan\Version;

class AttachmentTest extends \PHPUnit_Framework_TestCase {
    use TestCompareWithSignatureTrait;

    const USAGE_TYPE     = 'http://id.tincanapi.com/attachment/supporting_media';
    const DISPLAY        = 'testDisplay';
    const DESCRIPTION    = 'Test description.';
    const FILE_URL       = 'http://tincanapi.com/tincanphp/attachment/fileUrl';
    const CONTENT_TYPE   = 'text/plain';
    const CONTENT_STR    = 'some text content';
    const CONTENT_SHA2   = 'bd1a58265d96a3d1981710dab8b1e1ed04a8d7557ea53ab0cf7b44c04fd01545';
    const CONTENT_LENGTH = 17;

    private $emptyProperties = array(
        'usageType',
        'contentType',
        'length',
        'sha2',
        'fileUrl',
    );

    private $nonEmptyProperties = array(
        'display',
        'description',
    );

    public function testInstantiation() {
        $obj = new Attachment();
        $this->assertInstanceOf('TinCan\Attachment', $obj);
        foreach ($this->emptyProperties as $property) {
            $this->assertAttributeEmpty($property, $obj, "$property empty");
        }
        foreach ($this->nonEmptyProperties as $property) {
            $this->assertAttributeNotEmpty($property, $obj, "$property not empty");
        }
    }

    public function testUsesArraySetterTrait() {
        $this->assertContains('TinCan\ArraySetterTrait', class_uses('TinCan\Attachment'));
    }

    public function testUsesFromJSONTrait() {
        $this->assertContains('TinCan\FromJSONTrait', class_uses('TinCan\Attachment'));
    }

    public function testUsesAsVersionTrait() {
        $this->assertContains('TinCan\AsVersionTrait', class_uses('TinCan\Attachment'));
    }

    public function testContent() {
        $obj = new Attachment();
        $obj->setContent(self::CONTENT_STR);

        $this->assertSame($obj->getContent(), self::CONTENT_STR, 'content body');
        $this->assertSame($obj->getLength(), self::CONTENT_LENGTH, 'length');
        $this->assertSame($obj->getSha2(), self::CONTENT_SHA2, 'sha2');
    }

    public function testHasContent() {
        $no_content = new Attachment();
        $this->assertFalse($no_content->hasContent());

        $has_content = new Attachment(
            [
                'content' => self::CONTENT_STR
            ]
        );
        $this->assertTrue($has_content->hasContent());

        $set_content = new Attachment();
        $set_content->setContent(self::CONTENT_STR);
        $this->assertTrue($set_content->hasContent());
    }

    // TODO: need more robust test (happy-path)
    public function testAsVersion() {
        $args = [
            'usageType'   => self::USAGE_TYPE,
            'display'     => ['en-US' => self::DISPLAY],
            'description' => ['en-US' => self::DESCRIPTION],
            'contentType' => self::CONTENT_TYPE,
            'length'      => self::CONTENT_LENGTH,
            'sha2'        => self::CONTENT_SHA2,
            'fileUrl'     => self::FILE_URL
        ];
        $obj = new Attachment($args);
        $versioned = $obj->asVersion(Version::latest());
        $this->assertEquals($versioned, $args, '1.0.0');

        $obj = new Attachment(
            [
                'content' => self::CONTENT_STR
            ]
        );
        $this->assertEquals(
            $obj->asVersion(Version::latest()),
            ['length' => self::CONTENT_LENGTH, 'sha2' => self::CONTENT_SHA2],
            'auto populated properties but content not returned'
        );
    }

    public function testAsVersionEmpty() {
        $args = [];

        $obj       = Attachment::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        $this->assertEquals($versioned, $args, "serialized version matches original");
    }

    public function testAsVersionEmptyLanguageMap() {
        $args      = ['display' => []];

        $obj       = Attachment::fromJSON(json_encode($args, JSON_UNESCAPED_SLASHES));
        $versioned = $obj->asVersion('1.0.0');

        unset($args['display']);

        $this->assertEquals($versioned, $args, "serialized version matches corrected");
    }

    public function testCompareWithSignature() {
        $full = [
            'usageType'   => self::USAGE_TYPE,
            'display'     => ['en-US' => self::DISPLAY],
            'description' => ['en-US' => self::DESCRIPTION],
            'contentType' => self::CONTENT_TYPE,
            'length'      => self::CONTENT_LENGTH,
            'sha2'        => self::CONTENT_SHA2,
            'fileUrl'     => self::FILE_URL
        ];

        $cases = [
            [
                'description' => 'all null',
                'objArgs'     => []
            ],
            [
                'description' => 'usageType',
                'objArgs'     => ['usageType' => self::USAGE_TYPE]
            ],
            [
                'description' => 'display',
                'objArgs'     => ['display' => self::DISPLAY]
            ],
            [
                'description' => 'description',
                'objArgs'     => ['description' => self::DESCRIPTION]
            ],
            [
                'description' => 'contentType',
                'objArgs'     => ['contentType' => self::CONTENT_TYPE]
            ],
            [
                'description' => 'length',
                'objArgs'     => ['length' => self::CONTENT_LENGTH]
            ],
            [
                'description' => 'sha2',
                'objArgs'     => ['sha2' => self::CONTENT_SHA2]
            ],
            [
                'description' => 'fileUrl',
                'objArgs'     => ['fileUrl' => self::FILE_URL]
            ],
            [
                'description' => 'all',
                'objArgs'     => $full
            ],

            //
            // display and description are language maps which we aren't
            // checking for meaningful differences, so even though they
            // differ they should not cause signature comparison to fail
            //
            [
                'description' => 'display only with difference',
                'objArgs'     => ['display' => [ 'en-US' => self::DISPLAY ]],
                'sigArgs'     => ['display' => [ 'en-US' => self::DISPLAY . ' invalid' ]]
            ],
            [
                'description' => 'description only with difference',
                'objArgs'     => ['description' => [ 'en-US' => self::DESCRIPTION ]],
                'sigArgs'     => ['description' => [ 'en-US' => self::DESCRIPTION . ' invalid' ]]
            ],

            [
                'description' => 'usageType only: mismatch',
                'objArgs'     => ['usageType' => self::USAGE_TYPE ],
                'sigArgs'     => ['usageType' => self::USAGE_TYPE . '/invalid' ],
                'reason'      => 'Comparison of usageType failed: value is not the same'
            ],
            [
                'description' => 'contentType only: mismatch',
                'objArgs'     => ['contentType' => self::CONTENT_TYPE ],
                'sigArgs'     => ['contentType' => 'application/octet-stream' ],
                'reason'      => 'Comparison of contentType failed: value is not the same'
            ],
            [
                'description' => 'length only: mismatch',
                'objArgs'     => ['length' => self::CONTENT_LENGTH ],
                'sigArgs'     => ['length' => self::CONTENT_LENGTH + 2 ],
                'reason'      => 'Comparison of length failed: value is not the same'
            ],
            [
                'description' => 'sha2 only: mismatch',
                'objArgs'     => ['sha2' => self::CONTENT_SHA2],
                'sigArgs'     => ['sha2' => self::CONTENT_SHA2 . self::CONTENT_SHA2],
                'reason'      => 'Comparison of sha2 failed: value is not the same'
            ],
            [
                'description' => 'fileUrl only: mismatch',
                'objArgs'     => ['fileUrl' => self::FILE_URL],
                'sigArgs'     => ['fileUrl' => self::FILE_URL . '/invalid'],
                'reason'      => 'Comparison of fileUrl failed: value is not the same'
            ],
            [
                'description' => 'full: usageType mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['usageType' => self::USAGE_TYPE . '/invalid']),
                'reason'      => 'Comparison of usageType failed: value is not the same'
            ],
            [
                'description' => 'full: contentType mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['contentType' => 'application/octet-stream']),
                'reason'      => 'Comparison of contentType failed: value is not the same'
            ],
            [
                'description' => 'full: length mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['length' => self::CONTENT_LENGTH + 2]),
                'reason'      => 'Comparison of length failed: value is not the same'
            ],
            [
                'description' => 'full: sha2 mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['sha2' => self::CONTENT_SHA2 . self::CONTENT_SHA2]),
                'reason'      => 'Comparison of sha2 failed: value is not the same'
            ],
            [
                'description' => 'full: fileUrl mismatch',
                'objArgs'     => $full,
                'sigArgs'     => array_replace($full, ['fileUrl' => self::FILE_URL . '/invalid']),
                'reason'      => 'Comparison of fileUrl failed: value is not the same'
            ]
        ];
        $this->runSignatureCases("TinCan\Attachment", $cases);
    }
}
