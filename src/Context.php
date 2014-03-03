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

class Context implements VersionableInterface {
    use ArraySetterTrait, FromJSONTrait, AsVersionTrait;

    protected $registration;
    protected $instructor;
    protected $team;
    protected $contextActivities;
    protected $revision;
    protected $platform;
    protected $language;
    protected $statement;
    protected $extensions;

    static private $directProps = array(
        'registration',
        'revision',
        'platform',
        'language',
    );
    static private $versionedProps = array(
        'instructor',
        'team',
        'contextActivities',
        'statement',
        'extensions',
    );

    public function __construct() {
        if (func_num_args() == 1) {
            $arg = func_get_arg(0);

            $this->_fromArray($arg);
        }

        foreach (
            [
                'contextActivities',
                'extensions',
            ] as $k
        ) {
            $method = 'set' . ucfirst($k);

            if (! isset($this->$k)) {
                $this->$method(array());
            }
        }
    }

    public function setRegistration($value) {
        if (! preg_match(Util::UUID_REGEX, $value)) {
            throw new InvalidArgumentException('arg1 must be a UUIDv4');
        }
        $this->registration = $value;
        return $this;
    }
    public function getRegistration() { return $this->registration; }

    public function setInstructor($value) {
        // TODO: can be Group
        if ($value instanceof Agent) {
            $this->instructor = $value;
        }
        else {
            $this->instructor = new Agent($value);
        }
        return $this;
    }
    public function getInstructor() { return $this->instructor; }

    public function setTeam($value) {
        if ($value instanceof Group) {
            $this->team = $value;
        }
        else {
            $this->team = new Group($value);
        }
        return $this;
    }
    public function getTeam() { return $this->team; }

    public function setContextActivities($value) {
        if ($value instanceof ContextActivities) {
            $this->contextActivities = $value;
        }
        else {
            $this->contextActivities = new ContextActivities($value);
        }
        return $this;
    }
    public function getContextActivities() { return $this->contextActivities; }

    public function setRevision($value) { $this->revision = $value; return $this; }
    public function getRevision() { return $this->revision; }
    public function setPlatform($value) { $this->platform = $value; return $this; }
    public function getPlatform() { return $this->platform; }
    public function setLanguage($value) { $this->language = $value; return $this; }
    public function getLanguage() { return $this->language; }

    public function setStatement($value) {
        if ($value instanceof StatementRef) {
            $this->statement = $value;
        }
        else {
            $this->statement = new StatementRef($value);
        }
        return $this;
    }
    public function getStatement() { return $this->statement; }

    public function setExtensions($value) {
        if ($value instanceof Extensions) {
            $this->extensions = $value;
        }
        else {
            $this->extensions = new Extensions($value);
        }
        return $this;
    }
    public function getExtensions() { return $this->extensions; }
}
