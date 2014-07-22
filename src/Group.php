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

class Group extends Agent
{
    protected $objectType = 'Group';

    protected $member;

    public function __construct() {
        call_user_func_array('parent::__construct', func_get_args());

        if (! isset($this->member)) {
            $this->setMember(array());
        }
    }

    public function asVersion($version) {
        $result = parent::asVersion($version);

        if (count($this->member) > 0) {
            $result['member'] = array();

            foreach ($this->member as $v) {
                array_push($result['member'], $v->asVersion($version));
            }
        }

        return $result;
    }

    public function setMember($value) {
        foreach ($value as $k => $v) {
            if (! $v instanceof Agent) {
                $value[$k] = new Agent($v);
            }
        }

        $this->member = $value;

        return $this;
    }
    public function getMember() { return $this->member; }
    public function addMember($value) {
        if (! $value instanceof Agent) {
            $value = new Agent($value);
        }

        array_push($this->member, $value);

        return $this;
    }
}
