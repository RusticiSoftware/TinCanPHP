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

use TinCan\Activity;
use TinCan\RemoteLRS;
use TinCan\Statement;
use TinCan\Util;

class StatementVariationsTest extends \PHPUnit_Framework_TestCase {
    static protected $lrss;

    static public function setUpBeforeClass() {
        self::$lrss = [];

        foreach ($GLOBALS['LRSs'] as $lrs_cfg) {
            array_push(self::$lrss, new RemoteLRS($lrs_cfg));
        }
    }

    public function testBasic() {
        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementVariationsTest/Basic'
                ])
            ]
        );

        foreach (self::$lrss as $lrs) {
            $response = $lrs->saveStatement($statement);
            $this->assertInstanceOf('TinCan\LRSResponse', $response);
            $this->assertTrue($response->success, "successful request");
        }
    }

    public function testAttachmentsMetaOnly() {
        $text_content = "Content created at: " . Util::getTimestamp();

        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementVariationsTest/Basic'
                ]),
                'attachments' => [
                    [
                        'usageType'   => 'http://id.tincanapi.com/attachment/supporting_media',
                        'display'     => ['en-US' => 'StatementVariantsTest::testAttachmentsMetaOnly'],
                        'contentType' => 'text/plain; charset=ascii',
                        'length'      => 25,
                        'sha2'        => hash('sha256', $text_content),
                        'fileUrl'     => 'http://tincanapi.com/TinCanPHP/Test/AttachmentFileUrl'
                    ]
                ]
            ]
        );

        foreach (self::$lrss as $lrs) {
            $response = $lrs->saveStatement($statement);
            $this->assertInstanceOf('TinCan\LRSResponse', $response);
            $this->assertTrue($response->success, "successful request");
        }
    }

    public function testAttachmentsString() {
        $text_content = "Content created at: " . Util::getTimestamp();

        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementVariationsTest/AttachmentsString'
                ]),
                'attachments' => [
                    [
                        'usageType'   => 'http://id.tincanapi.com/attachment/supporting_media',
                        'display'     => ['en-US' => 'StatementVariantsTest::testAttachmentsString'],
                        'contentType' => 'text/plain; charset=ascii',
                        'content'     => $text_content
                    ]
                ]
            ]
        );

        foreach (self::$lrss as $lrs) {
            $response = $lrs->saveStatement($statement);
            $this->assertInstanceOf('TinCan\LRSResponse', $response);
            $this->assertTrue($response->success, "successful request");
        }
    }

    public function testAttachmentsBinaryFile() {
        $file = 'tests/files/image.jpg';

        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementVariationsTest/AttachmentsBinary'
                ]),
                'attachments' => [
                    [
                        'usageType'   => 'http://id.tincanapi.com/attachment/supporting_media',
                        'display'     => ['en-US' => 'StatementVariantsTest::testAttachmentsString'],
                        'contentType' => 'text/plain; charset=ascii',
                        'content'     => file_get_contents($file)
                    ]
                ]
            ]
        );

        foreach (self::$lrss as $lrs) {
            $saveResponse = $lrs->saveStatement($statement);
            $this->assertInstanceOf('TinCan\LRSResponse', $saveResponse);
            $this->assertTrue($saveResponse->success, "successful request");

            $retrieveResponse = $lrs->retrieveStatement($saveResponse->content->getId(), ['attachments' => true]);
            $this->assertInstanceOf('TinCan\LRSResponse', $retrieveResponse);
            $this->assertTrue($retrieveResponse->success);
            $this->assertInstanceOf('TinCan\Statement', $retrieveResponse->content);

            $this->assertSame($retrieveResponse->content->getAttachments()[0]->getSha2(), hash('sha256', file_get_contents($file)), 'verify content');
        }
    }

    public function testSignedAndVerified() {
        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementVariationsTest/Signed'
                ])
            ]
        );
        $statement->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password']);

        foreach (self::$lrss as $lrs) {
            $saveResponse = $lrs->saveStatement($statement);
            $this->assertInstanceOf('TinCan\LRSResponse', $saveResponse);
            $this->assertTrue($saveResponse->success, "successful request");

            $retrieveResponse = $lrs->retrieveStatement($saveResponse->content->getId(), ['attachments' => true]);
            $this->assertInstanceOf('TinCan\LRSResponse', $retrieveResponse);
            $this->assertTrue($retrieveResponse->success);
            $this->assertInstanceOf('TinCan\Statement', $retrieveResponse->content);

            $verificationResult = $retrieveResponse->content->verify(['publicKey' => 'file://' . $GLOBALS['KEYs']['public']]);
            $this->assertTrue($verificationResult['success'], 'verify signature');
        }
    }

    public function testSignedAndVerifiedX5c() {
        $statement = new Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new Activity([
                    'id' => COMMON_ACTIVITY_ID . '/StatementVariationsTest/Signed'
                ])
            ]
        );
        $statement->sign('file://' . $GLOBALS['KEYs']['private'], $GLOBALS['KEYs']['password'], ['x5c' => 'file://' . $GLOBALS['KEYs']['public']]);

        foreach (self::$lrss as $lrs) {
            $saveResponse = $lrs->saveStatement($statement);
            $this->assertInstanceOf('TinCan\LRSResponse', $saveResponse);
            if (! $saveResponse->success) {
                print_r($saveResponse);
            }
            $this->assertTrue($saveResponse->success, "successful request");

            $retrieveResponse = $lrs->retrieveStatement($saveResponse->content->getId(), ['attachments' => true]);
            $this->assertInstanceOf('TinCan\LRSResponse', $retrieveResponse);
            $this->assertTrue($retrieveResponse->success);
            $this->assertInstanceOf('TinCan\Statement', $retrieveResponse->content);

            $verificationResult = $retrieveResponse->content->verify();
            $this->assertTrue($verificationResult['success'], 'verify signature');
        }
    }
}
