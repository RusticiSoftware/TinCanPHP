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

namespace TinCan;

class LRSResponse
{
    use ArraySetterTrait;

    public $success;
    public $content;
    public $httpResponse;

    public function __construct($success, $content, $httpResponse) {
        $this->success = (bool) $success;
        $this->content = $content;
        $this->httpResponse = $httpResponse;
    }

    public static function fromRemoteLRSResponse(array $response, array $options, $content) {
        $success = false;

        if (($response['status'] >= 200 && $response['status'] < 300) || ($response['status'] === 404 && isset($options['ignore404']) && $options['ignore404'])) {
                $success = true;
        }
        elseif ($response['status'] >= 300 && $response['status'] < 400) {
                $content = "Unsupported status code: " . $response['status'] . " (LRS should not redirect)";
        }
        return new LRSResponse($success, $content, $response);
    }
}
