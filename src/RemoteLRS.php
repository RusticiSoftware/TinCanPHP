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

class RemoteLRS implements LRSInterface {
    protected $endpoint;
    protected $version;
    protected $auth;
    protected $extended;

    public function __construct() {
        $_num_args = func_num_args();
        if ($_num_args == 1) {
            $arg = func_get_arg(0);

            if ($arg['endpoint'] != null) {
                $this->setEndpoint($arg['endpoint']);
            }
            if ($arg['version'] != null) {
                $this->setVersion($arg['version']);
            }
            else {
                $this->setVersion(Version::latest());
            }
            if ($arg['auth'] != null) {
                $this->setAuth($arg['auth']);
            }
            if ($arg['extended'] != null) {
                $this->setExtended($arg['extended']);
            }
        }
        else if ($_num_args === 3) {
            $this->setEndpoint(func_get_arg(0));
            $this->setVersion(func_get_arg(1));
            $this->setAuth(func_get_arg(2));
        }
        else {
            $this->setVersion(Version::latest());
        }
    }

    public function about() {
        $resp = $this->sendRequest('PUT', 'about');

        return $resp;
    }

    public function saveStatement($statement) {
        $strSt = json_encode($statement->asVersion($this->version), JSON_UNESCAPED_SLASHES);

        $resp = $this->sendRequest(
            ($statement->hasId() ? 'PUT' : 'POST'),
            'statements' . ($statement->hasId() ? '?statementId=' . $statement->getId() : ''),
            array(
                'headers' => array(
                    'Content-type' => 'application/json'
                ),
                'content' => $strSt,
            )
        );
        return $resp;
    }

    public function saveStatements($statements) { throw new Exception('Feature not implemented'); }
    public function retrieveStatement($id) { throw new Exception('Feature not implemented'); }
    public function retrieveVoidedStatement($id) { throw new Exception('Feature not implemented'); }
    public function queryStatements($query) { throw new Exception('Feature not implemented'); }
    public function moreStatements($moreURL) { throw new Exception('Feature not implemented'); }

    public function retrieveStateKeys() { throw new Exception('Feature not implemented'); }
    public function retrieveState() { throw new Exception('Feature not implemented'); }
    public function saveState() { throw new Exception('Feature not implemented'); }
    public function deleteState() { throw new Exception('Feature not implemented'); }

    public function retrieveActivityProfileKeys() { throw new Exception('Feature not implemented'); }
    public function retrieveActivityProfile() { throw new Exception('Feature not implemented'); }
    public function saveActivityProfile() { throw new Exception('Feature not implemented'); }
    public function deleteActivityProfile() { throw new Exception('Feature not implemented'); }

    public function retrieveAgentProfileKeys() { throw new Exception('Feature not implemented'); }
    public function retrieveAgentProfile() { throw new Exception('Feature not implemented'); }
    public function saveAgentProfile() { throw new Exception('Feature not implemented'); }
    public function deleteAgentProfile() { throw new Exception('Feature not implemented'); }

    protected function sendRequest($method, $resource) {
        $options = func_num_args() === 3 ? func_get_arg(2) : array();

        $url = $this->endpoint . $resource;
        $http = array(
            'max_redirects' => 0,
            'request_fulluri' => 1,
            'ignore_errors' => true,
            'method' => $method,
            'header' => array(
                'X-Experience-API-Version: ' . $this->version
            ),
        );
        if (isset($this->auth)) {
            array_push($http['header'], 'Authorization: ' . $this->auth);
        }

        if (($method === 'PUT' || $method === 'POST') && isset($options['content'])) {
            $http['content'] = $options['content'];
            if (is_string($options['content'])) {
                array_push($http['header'], 'Content-length: ' . strlen($options['content']));
            }
        }
        if (isset($options['headers'])) {
            foreach ($options['headers'] as $k => $v) {
                array_push($http['header'], "$k: $v");
            }
        }
        print $http['method'] . " " . $url . "\n";
        var_dump($http);

        $context = stream_context_create(array( 'http' => $http ));
        $fp = fopen($url, 'rb', false, $context);
        if (! $fp) {
            throw new \Exception("Request fialed: $php_errormsg");
        }

        $metadata = stream_get_meta_data($fp);
        $content = stream_get_contents($fp);

        // TODO: make this a class based object? does PHP have one?
        return array(
            'metadata' => $metadata,
            'content' => $content,
        );
    }

    // FEATURE: check is URL
    public function setEndpoint($value) {
        if (substr($value, -1) != "/") {
            $value .= "/";
        }
        $this->endpoint = $value;
        return $this;
    }
    public function getEndpoint() { return $this->endpoint; }

    public function setVersion($value) {
        if (! in_array($value, Version::supported(), true)) {
            throw new \InvalidArgumentException("Unsupported version: $value");
        }
        $this->version = $value;
        return $this;
    }
    public function getVersion() { return $this->version; }

    public function setAuth() {
        $_num_args = func_num_args();
        if ($_num_args == 1) {
            $this->auth = func_get_arg(0);
        }
        elseif ($_num_args == 2) {
            $this->auth = 'Basic ' . base64_encode(func_get_arg(0) . ':' . func_get_arg(1));
        }
        else {
            throw new \BadMethodCallException('setAuth requires 1 or 2 arguments');
        }
        return $this;
    }
    public function getAuth() { return $this->auth; }
}
