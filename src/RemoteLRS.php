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

class RemoteLRS implements LRSInterface
{
    use ArraySetterTrait;

    private static $whitelistedHeaders = array(
        'Content-Type'                        => 'contentType',
        'Date'                                => 'date',
        'Last-Modified'                       => 'lastModified',
        'Etag'                                => 'etag',
        'X-Experience-API-Consistent-Through' => 'apiConsistentThrough',
        'X-Experience-API-Version'            => 'apiVersion',
    );
    protected $endpoint;
    protected $version;
    protected $auth;
    protected $extended;

    public function __construct() {
        $_num_args = func_num_args();
        if ($_num_args == 1) {
            $arg = func_get_arg(0);

            $this->_fromArray($arg);

            if (! isset($this->version)) {
                $this->setVersion(Version::latest());
            }
            if (! isset($this->auth) && isset($arg['username']) && isset($arg['password'])) {
                $this->setAuth($arg['username'], $arg['password']);
            }
        }
        elseif ($_num_args === 3) {
            $this->setEndpoint(func_get_arg(0));
            $this->setVersion(func_get_arg(1));
            $this->setAuth(func_get_arg(2));
        }
        elseif ($_num_args === 4) {
            $this->setEndpoint(func_get_arg(0));
            $this->setVersion(func_get_arg(1));
            $this->setAuth(func_get_arg(2), func_get_arg(3));
        }
        else {
            $this->setVersion(Version::latest());
        }
    }

    protected function sendRequest($method, $resource) {
        $options = func_num_args() === 3 ? func_get_arg(2) : array();

        //
        // allow for full path requests, for instance as used by the
        // moreStatements method which is based on server root rather
        // than the stored endpoint
        //
        $url = $resource;
        if (! preg_match('/^http/', $resource)) {
            $url = $this->endpoint . $resource;
        }
        $http = array(
            //
            // redirects are not part of the spec so LRSs shouldn't be returning them
            //
            'max_redirects' => 0,

            //
            // this is here for some proxy handling
            //
            'request_fulluri' => 1,

            //
            // switching this to false causes non-2xx/3xx status codes to throw exceptions
            // but we need to handle the "error" status codes ourselves in some cases
            //
            'ignore_errors' => true,

            'method' => $method,
            'header' => array(
                'X-Experience-API-Version: ' . $this->version
            ),
        );
        if (isset($this->auth)) {
            array_push($http['header'], 'Authorization: ' . $this->auth);
        }

        if (isset($options['headers'])) {
            foreach ($options['headers'] as $k => $v) {
                array_push($http['header'], "$k: $v");
            }
        }
        if (isset($options['params']) && count($options['params']) > 0) {
            $url .= '?' . http_build_query($options['params'], null, '&', PHP_QUERY_RFC3986);
        }

        if (($method === 'PUT' || $method === 'POST') && isset($options['content'])) {
            $http['content'] = $options['content'];
            if (is_string($options['content'])) {
                array_push($http['header'], 'Content-length: ' . strlen($options['content']));
            }
        }

        $context = stream_context_create(array( 'http' => $http ));
        $fp = fopen($url, 'rb', false, $context);
        if (! $fp) {
            throw new \Exception("Request failed: $php_errormsg");
        }

        //
        // FEATURE: handle attachments via multipart
        //
        $metadata = stream_get_meta_data($fp);
        $content  = stream_get_contents($fp);

        $response = $this->_parseMetadata($metadata, $options);

        //
        // keep a copy of the raw content, the methods expecting
        // an LRS response may handle the content, for instance
        // querying statements takes the returned value and converts
        // it to Statement objects (really StatementsResult but who
        // is counting), etc. but a user may want the original raw
        // returned content untouched, do the same with the metadata
        // because it feels like a good practice
        //
        $response['_content']  = $content;
        $response['_metadata'] = $metadata;

        $success = false;
        if (($response['status'] >= 200 && $response['status'] < 300) || ($response['status'] === 404 && $options['ignore404'])) {
            $success = true;
        }
        elseif ($response['status'] >= 300 && $response['status'] < 400) {
            throw new \Exception("Unsupported status code: " . $response['status'] . " (LRS should not redirect)");
        }

        return new LRSResponse($success, $content, $response);
    }

    private function _parseMetadata($metadata) {
        $result = array();

        $status_line = array_shift($metadata['wrapper_data']);
        $status_parts = explode(' ', $status_line);
        $result['status'] = intval($status_parts[1]);

        //
        // pull out whitelisted headers
        //
        foreach (self::$whitelistedHeaders as $header => $prop) {
            foreach ($metadata['wrapper_data'] as $line) {
                if (stripos($line, $header . ':') === 0) {
                    $result['headers'][$prop] = ltrim(substr($line, (strlen($header . ':'))));
                    break;
                }
            }
        }

        // TODO: handle content type stripping the charset:
        if (isset($result['headers']['contentType'])) {
            $contentType_parts = explode(';', $result['headers']['contentType']);

            $result['headers']['contentType'] = $contentType_parts[0];
            if (isset($contentType_parts[1]) && preg_match('/^charset/', $contentType_parts[1])) {
                $result['headers']['contentTypeCharset'] = ltrim($contentType_parts[1]);
            }
        }

        return $result;
    }

    public function about() {
        $response = $this->sendRequest('GET', 'about');

        if ($response->success) {
            $response->content = About::FromJSON($response->content);
        }

        return $response;
    }

    public function saveStatement($statement) {
        if (! $statement instanceof Statement) {
            $statement = new Statement($statement);
        }

        $requestCfg = array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'content' => json_encode($statement->asVersion($this->version), JSON_UNESCAPED_SLASHES),
        );

        $method = 'POST';
        if ($statement->hasId()) {
            $method = 'PUT';
            $requestCfg['params'] = array('statementId' => $statement->getId());
        }

        $response = $this->sendRequest($method, 'statements', $requestCfg);

        if ($response->success) {
            if (! $statement->hasId()) {
                $parsed_content = json_decode($response->content, true);

                $statement->setId($parsed_content[0]);
            }

            //
            // save statement either returns no content when there is an id
            // or returns the id when there wasn't, either way the caller
            // may have called us with a statement configuration rather than
            // a Statement object, so provide them back the Statement object
            // as the content in either case on succcess
            //
            $response->content = $statement;
        }

        return $response;
    }

    public function saveStatements($statements) {
        $versioned_statements = array();
        foreach ($statements as $i => $st) {
            if (! $st instanceof Statement) {
                $st = new Statement($st);
                $statements[$i] = $st;
            }
            $versioned_statements[$i] = $st->asVersion($this->version);
        }

        $requestCfg = array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'content' => json_encode($versioned_statements, JSON_UNESCAPED_SLASHES),
        );

        $response = $this->sendRequest('POST', 'statements', $requestCfg);

        if ($response->success) {
            $parsed_content = json_decode($response->content, true);
            foreach ($parsed_content as $i => $stId) {
                $statements[$i]->setId($stId);
            }

            $response->content = $statements;
        }

        return $response;
    }

    public function retrieveStatement($id) {
        $response = $this->sendRequest(
            'GET',
            'statements',
            array(
                'params' => array(
                    'statementId' => $id
                )
            )
        );

        if ($response->success) {
            $response->content = Statement::FromJSON($response->content);
        }

        return $response;
    }

    public function retrieveVoidedStatement($id) {
        $response = $this->sendRequest(
            'GET',
            'statements',
            array(
                'params' => array(
                    'voidedStatementId' => $id
                )
            )
        );

        if ($response->success) {
            $response->content = Statement::FromJSON($response->content);
        }

        return $response;
    }

    private function queryStatementsRequestParams($query) {
        $result = array();

        foreach (array('agent') as $k) {
            if (isset($query[$k])) {
                $result[$k] = json_encode($query[$k]->asVersion($this->version));
            }
        }
        foreach (
            array(
                'verb',
                'activity',
            ) as $k
        ) {
            if (isset($query[$k])) {
                $result[$k] = $query[$k]->getId();
            }
        }
        foreach (
            array(
                'registration',
                'since',
                'until',
                'limit',
                'ascending',
                'related_activities',
                'related_agents',
                'format',
                'attachments',
            ) as $k
        ) {
            if (isset($query[$k])) {
                $result[$k] = $query[$k];
            }
        }

        return $result;
    }

    public function queryStatements($query) {
        $requestCfg = array(
            'params' => $this->queryStatementsRequestParams($query),
        );

        $response = $this->sendRequest('GET', 'statements', $requestCfg);

        if ($response->success) {
            $response->content = StatementsResult::fromJSON($response->content);
        }

        return $response;
    }

    public function moreStatements($moreUrl) {
        if ($moreUrl instanceof StatementsResult) {
            $moreUrl = $moreUrl->getMore();
        }
        $moreUrl = $this->getEndpointServerRoot() . $moreUrl;

        $response = $this->sendRequest('GET', $moreUrl);

        if ($response->success) {
            $response->content = StatementsResult::fromJSON($response->content);
        }

        return $response;
    }

    public function retrieveStateIds($activity, $agent) {
        if (! $activity instanceof Activity) {
            $activity = new Activity($activity);
        }
        if (! $agent instanceof Agent) {
            $agent = new Agent($agent);
        }

        $requestCfg = array(
            'params' => array(
                'activityId' => $activity->getId(),
                'agent'      => json_encode($agent->asVersion($this->version)),
            ),
        );
        if (func_num_args() > 2) {
            $options = func_get_arg(2);
            if (isset($options)) {
                if (isset($options['registration'])) {
                    $requestCfg['params']['registration'] = $options['registration'];
                }
                if (isset($options['since'])) {
                    $requestCfg['params']['since'] = $options['since'];
                }
            }
        }

        $response = $this->sendRequest('GET', 'activities/state', $requestCfg);

        if ($response->success) {
            $response->content = json_decode($response->content);
        }

        return $response;
    }

    public function retrieveState($activity, $agent, $id) {
        if (! $activity instanceof Activity) {
            $activity = new Activity($activity);
        }
        if (! $agent instanceof Agent) {
            $agent = new Agent($agent);
        }
        $registration = null;

        $requestCfg = array(
            'params' => array(
                'activityId' => $activity->getId(),
                'agent'      => json_encode($agent->asVersion($this->version)),
                'stateId'    => $id,
            ),
            'ignore404' => true,
        );
        if (func_num_args() > 3) {
            $options = func_get_arg(3);
            if (isset($options)) {
                if (isset($options['registration'])) {
                    $requestCfg['params']['registration'] = $registration = $options['registration'];
                }
            }
        }

        $response = $this->sendRequest('GET', 'activities/state', $requestCfg);

        if ($response->success) {
            $doc = new State(
                array(
                    'id'       => $id,
                    'content'  => $response->content,
                    'activity' => $activity,
                    'agent'    => $agent,
                )
            );
            if (isset($registration)) {
                $doc->setRegistration($registration);
            }
            if (isset($response->httpResponse['headers']['lastModified'])) {
                $doc->setTimestamp($response->httpResponse['headers']['lastModified']);
            }
            if (isset($response->httpResponse['headers']['contentType'])) {
                $doc->setContentType($response->httpResponse['headers']['contentType']);
            }
            if (isset($response->httpResponse['headers']['etag'])) {
                $doc->setEtag($response->httpResponse['headers']['etag']);
            }

            $response->content = $doc;
        }

        return $response;
    }

    public function saveState($activity, $agent, $id, $content) {
        if (! $activity instanceof Activity) {
            $activity = new Activity($activity);
        }
        if (! $agent instanceof Agent) {
            $agent = new Agent($agent);
        }

        $contentType = 'application/octet-stream';

        $requestCfg = array(
            'headers' => array(
                'Content-Type' => $contentType,
            ),
            'params' => array(
                'activityId' => $activity->getId(),
                'agent'      => json_encode($agent->asVersion($this->version)),
                'stateId'    => $id,
            ),
            'content' => $content,
        );
        $registration = null;
        if (func_num_args() > 4) {
            $options = func_get_arg(4);
            if (isset($options)) {
                if (isset($options['contentType'])) {
                    $requestCfg['headers']['Content-Type'] = $contentType = $options['contentType'];
                }
                if (isset($options['etag'])) {
                    $requestCfg['headers']['If-Match'] = $options['etag'];
                }
                if (isset($options['registration'])) {
                    $requestCfg['params']['registration'] = $registration = $options['registration'];
                }
            }
        }

        $response = $this->sendRequest('PUT', 'activities/state', $requestCfg);

        if ($response->success) {
            $doc = new State(
                array(
                    'id'          => $id,
                    'content'     => $content,
                    'contentType' => $contentType,
                    'etag'        => sha1($content),
                    'activity'    => $activity,
                    'agent'       => $agent,
                )
            );
            if (isset($registration)) {
                $doc->setRegistration($registration);
            }
            if (isset($response->httpResponse['headers']['date'])) {
                $doc->setTimestamp($response->httpResponse['headers']['date']);
            }

            $response->content = $doc;
        }

        return $response;
    }

    //
    // this is a separate private method because the implementation
    // of deleteState and clearState are essentially identical but
    // I didn't want to make it easy to call deleteState accidentally
    // without an id therefore clearing all of the state when only
    // one id was desired to be deleted, so clearState is an explicit
    // separate method signature
    //
    // TODO: Etag?
    private function _deleteState($activity, $agent, $id) {
        if (! $activity instanceof Activity) {
            $activity = new Activity($activity);
        }
        if (! $agent instanceof Agent) {
            $agent = new Agent($agent);
        }

        $requestCfg = array(
            'params' => array(
                'activityId' => $activity->getId(),
                'agent'      => json_encode($agent->asVersion($this->version)),
            )
        );
        if (isset($id)) {
            $requestCfg['params']['stateId'] = $id;
        }

        if (func_num_args() > 3) {
            $options = func_get_arg(3);
            if (isset($options)) {
                if (isset($options['registration'])) {
                    $requestCfg['params']['registration'] = $options['registration'];
                }
            }
        }

        $response = $this->sendRequest('DELETE', 'activities/state', $requestCfg);

        return $response;
    }

    public function deleteState($activity, $agent, $id) {
        return call_user_func_array(array($this, '_deleteState'), func_get_args());
    }

    public function clearState($activity, $agent) {
        $args = array($activity, $agent, null);

        $numArgs = func_num_args();
        if ($numArgs > 2) {
            $args = array_merge($args, array_slice(func_get_args(), 2));
        }

        return call_user_func_array(array($this, '_deleteState'), $args);
    }

    public function retrieveActivityProfileIds($activity) {
        if (! $activity instanceof Activity) {
            $activity = new Activity($activity);
        }

        $requestCfg = array(
            'params' => array(
                'activityId' => $activity->getId()
            )
        );
        if (func_num_args() > 1) {
            $options = func_get_arg(1);
            if (isset($options)) {
                if (isset($options['since'])) {
                    $requestCfg['params']['since'] = $options['since'];
                }
            }
        }

        $response = $this->sendRequest('GET', 'activities/profile', $requestCfg);

        if ($response->success) {
            $response->content = json_decode($response->content);
        }

        return $response;
    }

    public function retrieveActivityProfile($activity, $id) {
        if (! $activity instanceof Activity) {
            $activity = new Activity($activity);
        }
        $response = $this->sendRequest(
            'GET',
            'activities/profile',
            array(
                'params' => array(
                    'activityId' => $activity->getId(),
                    'profileId'  => $id,
                ),
                'ignore404' => true,
            )
        );

        if ($response->success) {
            $doc = new ActivityProfile(
                array(
                    'id'       => $id,
                    'content'  => $response->content,
                    'activity' => $activity,
                )
            );
            if (isset($response->httpResponse['headers']['lastModified'])) {
                $doc->setTimestamp($response->httpResponse['headers']['lastModified']);
            }
            if (isset($response->httpResponse['headers']['contentType'])) {
                $doc->setContentType($response->httpResponse['headers']['contentType']);
            }
            if (isset($response->httpResponse['headers']['etag'])) {
                $doc->setEtag($response->httpResponse['headers']['etag']);
            }

            $response->content = $doc;
        }

        return $response;
    }

    public function saveActivityProfile($activity, $id, $content) {
        if (! $activity instanceof Activity) {
            $activity = new Activity($activity);
        }

        $contentType = 'application/octet-stream';

        $requestCfg = array(
            'headers' => array(
                'Content-Type' => $contentType,
            ),
            'params' => array(
                'activityId' => $activity->getId(),
                'profileId'  => $id,
            ),
            'content' => $content,
        );
        if (func_num_args() > 3) {
            $options = func_get_arg(3);
            if (isset($options)) {
                if (isset($options['contentType'])) {
                    $requestCfg['headers']['Content-Type'] = $contentType = $options['contentType'];
                }
                if (isset($options['etag'])) {
                    $requestCfg['headers']['If-Match'] = $options['etag'];
                }
            }
        }

        $response = $this->sendRequest('PUT', 'activities/profile', $requestCfg);

        if ($response->success) {
            $doc = new ActivityProfile(
                array(
                    'id'          => $id,
                    'content'     => $content,
                    'contentType' => $contentType,
                    'etag'        => sha1($content),
                    'activity'    => $activity,
                )
            );
            if (isset($response->httpResponse['headers']['date'])) {
                $doc->setTimestamp($response->httpResponse['headers']['date']);
            }

            $response->content = $doc;
        }

        return $response;
    }

    // TODO: Etag?
    public function deleteActivityProfile($activity, $id) {
        if (! $activity instanceof Activity) {
            $activity = new Activity($activity);
        }
        $response = $this->sendRequest(
            'DELETE',
            'activities/profile',
            array(
                'params' => array(
                    'activityId' => $activity->getId(),
                    'profileId'  => $id,
                ),
            )
        );

        return $response;
    }

    // TODO: groups?
    public function retrieveAgentProfileIds($agent) {
        if (! $agent instanceof Agent) {
            $agent = new Agent($agent);
        }

        $requestCfg = array(
            'params' => array(
                'agent' => json_encode($agent->asVersion($this->version))
            )
        );
        if (func_num_args() > 1) {
            $options = func_get_arg(1);
            if (isset($options)) {
                if (isset($options['since'])) {
                    $requestCfg['params']['since'] = $options['since'];
                }
            }
        }

        $response = $this->sendRequest('GET', 'agents/profile', $requestCfg);

        if ($response->success) {
            $response->content = json_decode($response->content);
        }

        return $response;
    }

    public function retrieveAgentProfile($agent, $id) {
        // TODO: Group
        if (! $agent instanceof Agent) {
            $agent = new Agent($agent);
        }
        $response = $this->sendRequest(
            'GET',
            'agents/profile',
            array(
                'params' => array(
                    'agent'     => json_encode($agent->asVersion($this->version)),
                    'profileId' => $id,
                ),
                'ignore404' => true,
            )
        );

        if ($response->success) {
            $doc = new AgentProfile(
                array(
                    'id'      => $id,
                    'content' => $response->content,
                    'agent'   => $agent,
                )
            );
            if (isset($response->httpResponse['headers']['lastModified'])) {
                $doc->setTimestamp($response->httpResponse['headers']['lastModified']);
            }
            if (isset($response->httpResponse['headers']['contentType'])) {
                $doc->setContentType($response->httpResponse['headers']['contentType']);
            }
            if (isset($response->httpResponse['headers']['etag'])) {
                $doc->setEtag($response->httpResponse['headers']['etag']);
            }

            $response->content = $doc;
        }

        return $response;
    }

    public function saveAgentProfile($agent, $id, $content) {
        // TODO: Group
        if (! $agent instanceof Agent) {
            $agent = new Agent($agent);
        }

        $contentType = 'application/octet-stream';

        $requestCfg = array(
            'headers' => array(
                'Content-Type' => $contentType,
            ),
            'params' => array(
                'agent'     => json_encode($agent->asVersion($this->version)),
                'profileId' => $id,
            ),
            'content' => $content,
        );
        if (func_num_args() > 3) {
            $options = func_get_arg(3);
            if (isset($options)) {
                if (isset($options['contentType'])) {
                    $requestCfg['headers']['Content-Type'] = $contentType = $options['contentType'];
                }
                if (isset($options['etag'])) {
                    $requestCfg['headers']['If-Match'] = $options['etag'];
                }
            }
        }

        $response = $this->sendRequest('PUT', 'agents/profile', $requestCfg);

        if ($response->success) {
            $doc = new AgentProfile(
                array(
                    'id' => $id,
                    'content' => $content,
                    'contentType' => $contentType,
                    'etag' => sha1($content),
                    'agent' => $agent,
                )
            );
            if (isset($response->httpResponse['headers']['date'])) {
                $doc->setTimestamp($response->httpResponse['headers']['date']);
            }

            $response->content = $doc;
        }

        return $response;
    }

    // TODO: Etag?
    public function deleteAgentProfile($agent, $id) {
        // TODO: Group
        if (! $agent instanceof Agent) {
            $agent = new Agent($agent);
        }
        $response = $this->sendRequest(
            'DELETE',
            'agents/profile',
            array(
                'params' => array(
                    'agent'     => json_encode($agent->asVersion($this->version)),
                    'profileId' => $id,
                ),
            )
        );

        return $response;
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
    public function getEndpointServerRoot() {
        $parsed = parse_url($this->endpoint);

        $root = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) {
            $root .= ":" . $parsed['port'];
        }

        return $root;
    }

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
