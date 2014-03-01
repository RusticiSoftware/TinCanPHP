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

class Verb implements VersionableInterface {
    use FromJSONTrait;

    protected $id;
    protected $display;

    public function __construct() {
        if (func_num_args() == 1) {
            $arg = func_get_arg(0);

            if (isset($arg['id'])) {
                $this->setId($arg['id']);
            }
            if (isset($arg['display'])) {
                $this->setDisplay($arg['display']);
            }
        }
    }

    public function asVersion($version) {
        $result = array();
        if (isset($this->id)) {
            $result['id'] = $this->id;
        }
        if (isset($this->display)) {
            $result['display'] = $this->display;
        }

        return $result;
    }

    // FEATURE: check IRI?
    public function setId($value) { $this->id = $value; return $this; }
    public function getId() { return $this->id; }

    // TODO: provide display language interface rather than direct?
    public function setDisplay($value) { $this->display = $value; return $this; }
    public function getDisplay() { return $this->display; }
}
