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

use TinCan\LRSResponse;

class LRSResponseTest extends \PHPUnit_Framework_TestCase {

    public function testInstantiation() {
        $obj = new LRSResponse(true, '', false);
        $this->assertTrue($obj->success);
        $this->assertEquals('', $obj->content);
        $this->assertFalse($obj->httpResponse);
    }

    public function testFromRemoteLRSResponse()
    {

        $response = ['status' => 400];
        $options  = ['ignore404' => false];
        $content  = 'hi there!';

        $obj = LRSResponse::fromRemoteLRSResponse(
            $response,
            $options,
            $content
        );

        $this->assertFalse($obj->success);
        $this->assertEquals($content, $obj->content);

        $response = ['status' => 404];
        $options  = ['ignore404' => true];
        $content  = 'hi there!';

        $obj = LRSResponse::fromRemoteLRSResponse(
            $response,
            $options,
            $content
        );

        $this->assertTrue($obj->success);

        $response = ['status' => 200];
        $options  = ['ignore404' => false];
        $content  = 'hi there!';

        $obj = LRSResponse::fromRemoteLRSResponse(
            $response,
            $options,
            $content
        );

        $this->assertTrue($obj->success);

        $response = ['status' => 300];
        $options  = ['ignore404' => false];
        $content  = "Unsupported status code: {$response['status']} (LRS should not redirect)";

        $obj = LRSResponse::fromRemoteLRSResponse(
            $response,
            $options,
            $content
        );

        $this->assertFalse($obj->success);
        $this->assertEquals($content, $obj->content);
    }
}
