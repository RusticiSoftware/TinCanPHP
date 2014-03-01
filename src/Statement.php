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
    use FromJSONTrait;

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

            if (isset($arg['id'])) {
                $this->setId($arg['id']);
            }
            if (isset($arg['actor'])) {
                $this->setActor($arg['actor']);
            }
            if (isset($arg['verb'])) {
                $this->setVerb($arg['verb']);
            }
            if (isset($arg['target'])) {
                $this->setTarget($arg['target']);
            }
            if (isset($arg['object'])) {
                $this->setTarget($arg['object']);
            }
            if (isset($arg['timestamp'])) {
                $this->setTimestamp($arg['timestamp']);
            }
            if (isset($arg['stored'])) {
                $this->setStored($arg['stored']);
            }
        }
    }

    // TODO: make this a trait?
    public function asVersion($version) {
        $result = array();

        foreach (self::$directProps as $key) {
            if (isset($this->$key)) {
                $result[$key] = $this->$key;
            }
        }
        foreach (self::$versionedProps as $key) {
            if (isset($this->$key)) {
                $result[$key] = $this->$key->asVersion($version);
            }
        }

        if (isset($this->target)) {
            $result['object'] = $this->target->asVersion($version);
        }

        return $result;
    }

    public function stamp() {
        $this->setId(Util::getUUID());
        $this->setTimestamp(Util::getTimestamp());
    }

    // FEATURE: check IRI?
    public function setId($value) { $this->id = $value; return $this; }
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
}
