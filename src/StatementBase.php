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
/*  API Modified for CoursePress and WordPress minimum requirements. */

class TinCanAPI_StatementBase extends TinCanAPI_VersionableInterface {

    protected $actor;
    protected $verb;
    protected $target;
    protected $result;
    protected $context;

    //
    // timestamp *must* store a string because DateTime doesn't
    // support sub-second precision, the setter will take a DateTime and convert
    // it to the proper ISO8601 representation, but if a user needs sub-second
    // precision as afforded by the spec they will have to create their own,
    // they can see TinCan\Util::getTimestamp for an example of how to do so
    //
    protected $timestamp;

    public static $directProps = array(
        'timestamp',
    );
    public static $versionedProps = array(
        'actor',
        'verb',
        'result',
        'context',
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

    public function _asVersion(&$result, $version) {
        if (isset($this->target)) {
            $result['object'] = $this->target->asVersion($version);
        }
    }

    public function setActor($value) {
        if ((! $value instanceof TinCanAPI_Agent && ! $value instanceof TinCanAPI_Group) && is_array($value)) {
            if (isset($value['objectType']) && $value['objectType'] === 'TinCanAPI_Group') {
                $value = new TinCanAPI_Group($value);
            }
            else {
                $value = new TinCanAPI_Agent($value);
            }
        }

        $this->actor = $value;

        return $this;
    }
    public function getActor() { return $this->actor; }

    public function setVerb($value) {
        if (! $value instanceof TinCanAPI_Verb) {
            $value = new TinCanAPI_Verb($value);
        }

        $this->verb = $value;

        return $this;
    }
    public function getVerb() { return $this->verb; }

    public function setTarget($value) {
        if (! $value instanceof TinCanAPI_StatementTargetInterface && is_array($value)) {
            if (isset($value['objectType'])) {
                if ($value['objectType'] === 'Activity') {
                    $value = new TinCanAPI_Activity($value);
                }
                elseif ($value['objectType'] === 'Agent') {
                    $value = new TinCanAPI_Agent($value);
                }
                elseif ($value['objectType'] === 'Group') {
                    $value = new TinCanAPI_Group($value);
                }
                elseif ($value['objectType'] === 'StatementRef') {
                    $value = new TinCanAPI_StatementRef($value);
                }
                elseif ($value['objectType'] === 'SubStatement') {
                    $value = new TinCanAPI_SubStatement($value);
                }
                else {
                    throw new InvalidArgumentException('arg1 must implement the StatementTargetInterface objectType not recognized:' . $value['objectType']);
                }
            }
            else {
                $value = new TinCanAPI_Activity($value);
            }
        }

        $this->target = $value;

        return $this;
    }
    public function getTarget() { return $this->target; }

    // sugar methods
    public function setObject($value) { return $this->setTarget($value); }
    public function getObject() { return $this->getTarget(); }

    public function setResult($value) {
        if (! $value instanceof TinCanAPI_Result && is_array($value)) {
            $value = new TinCanAPI_Result($value);
        }

        $this->result = $value;

        return $this;
    }
    public function getResult() { return $this->result; }

    public function setContext($value) {
        if (! $value instanceof TinCanAPI_Context && is_array($value)) {
            $value = new TinCanAPI_Context($value);
        }

        $this->context = $value;

        return $this;
    }
    public function getContext() { return $this->context; }

    public function setTimestamp($value) {
        if (isset($value)) {
            if ($value instanceof DateTime) {
                $value = $value->format(DateTime::ISO8601);
            }
            elseif (is_string($value)) {
                $value = $value;
            }
            else {
                throw new InvalidArgumentException('type of arg1 must be string or DateTime');
            }
        }

        $this->timestamp = $value;

        return $this;
    }
    public function getTimestamp() { return $this->timestamp; }
}
