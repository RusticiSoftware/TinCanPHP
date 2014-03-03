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

class Statement implements VersionableInterface {
    use ArraySetterTrait, FromJSONTrait, AsVersionTrait;

    protected $id;
    protected $actor;
    protected $verb;
    protected $target;
    protected $result;
    protected $context;

    //
    // timestamp and stored *must* store a string because DateTime doesn't
    // support sub-second precision, the setter will take a DateTime and convert
    // it to the proper ISO8601 representation, but if a user needs sub-second
    // precision as afforded by the spec they will have to create their own,
    // they can see TinCan\Util::getTimestamp for an example of how to do so
    //
    protected $timestamp;
    protected $stored;

    protected $authority;
    protected $version;

    static private $directProps = array(
        'id',
        'timestamp',
        'stored',
        'version',
    );
    static private $versionedProps = array(
        'actor',
        'verb',
        'result',
        'context',
        'authority',
    );

    public function __construct() {
        if (func_num_args() == 1) {
            $arg = func_get_arg(0);

            $this->_fromArray($arg);

            //
            // 'object' isn't in the list of properties so ._fromArray doesn't
            // pick it up correctly, but 'target' and 'object' shouldn't be in
            // the args at the same time, so handle 'object' here
            //
            if (isset($arg['object'])) {
                $this->setObject($arg['object']);
            }
        }
    }

    private function _asVersion(&$result, $version) {
        if (isset($this->target)) {
            $result['object'] = $this->target->asVersion($version);
        }
    }

    public function stamp() {
        $this->setId(Util::getUUID());
        $this->setTimestamp(Util::getTimestamp());
    }

    public function setId($value) {
        if (! preg_match(Util::UUID_REGEX, $value)) {
            throw new InvalidArgumentException('arg1 must be a UUIDv4');
        }
        $this->id = $value;
        return $this;
    }
    public function getId() { return $this->id; }
    public function hasId() { return isset($this->id); }

    public function setActor($value) {
        // TODO: allow Group as passed or from construction
        if ($value instanceof Agent) {
            $this->actor = $value;
        }
        else {
            $this->actor = new Agent($value);
        }
        return $this;
    }
    public function getActor() { return $this->actor; }

    public function setVerb($value) {
        if ($value instanceof Verb) {
            $this->verb = $value;
        }
        else {
            $this->verb = new Verb($value);
        }
        return $this;
    }
    public function getVerb() { return $this->verb; }

    public function setTarget($value) {
        if ($value instanceof StatementTargetInterface) {
            $this->target = $value;
        }
        else {
            // FEATURE: allow them to pass something else identified by objectType?
            throw new \InvalidArgumentException('arg1 must implement the StatementTargetInterface');
        }
        return $this;
    }
    public function getTarget() { return $this->target; }

    // sugar methods
    public function setObject($value) { return $this->setTarget($value); }
    public function getObject() { return $this->getTarget(); }

    public function setResult($value) {
        if ($value instanceof Result) {
            $this->result = $value;
        }
        else {
            $this->result = new Result($value);
        }
        return $this;
    }
    public function getResult() { return $this->result; }

    public function setContext($value) {
        if ($value instanceof Context) {
            $this->context = $value;
        }
        else {
            $this->context = new Context($value);
        }
        return $this;
    }
    public function getContext() { return $this->context; }

    public function setTimestamp($value) {
        if ($value instanceof \DateTime) {
            $this->timestamp = $value->format(\DateTime::ISO8601);
        }
        else if (is_string($value)) {
            $this->timestamp = $value;
        }
        else {
            throw new \InvalidArgumentException('type of arg1 must be string or DateTime');
        }
        return $this;
    }
    public function getTimestamp() { return $this->timestamp; }

    public function setStored($value) {
        if ($value instanceof \DateTime) {
            $this->stored = $value->format(\DateTime::ISO8601);
        }
        else if (is_string($value)) {
            $this->stored = $value;
        }
        else {
            throw new \InvalidArgumentException('type of arg1 must be string or DateTime');
        }
        return $this;
    }
    public function getStored() { return $this->stored; }

    public function setAuthority($value) {
        if ($value instanceof Agent) {
            $this->authority = $value;
        }
        else {
            $this->authority = new Agent($value);
        }
        return $this;
    }
    public function getAuthority() { return $this->authority; }

    public function setVersion($value) { $this->version = $value; return $this; }
    public function getVersion() { return $this->version; }
}
