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

class Activity implements VersionableInterface, StatementTargetInterface {
    use FromJSONTrait;
    private $objectType = 'Activity';

    protected $id;
    protected $definition;

    public function __construct() {
        if (func_num_args() == 1) {
            $arg = func_get_arg(0);

            if (isset($arg['id'])) {
                $this->setId($arg['id']);
            }
            if (isset($arg['definition'])) {
                $this->setDefinition($arg['definition']);
            }
        }
    }

    public function asVersion($version) {
        $result = array(
            'objectType' => $this->objectType
        );
        if (isset($this->id)) {
            $result['id'] = $this->id;
        }
        if (isset($this->definition)) {
            $result['definition'] = $this->definition;
        }

        return $result;
    }

    public function getObjectType() { return $this->objectType; }

    // FEATURE: check IRI?
    public function setId($value) { $this->id = $value; return $this; }
    public function getId() { return $this->id; }

    public function setDefinition($value) { $this->definition = $value; return $this; }
    public function getDefinition() { return $this->definition; }
}
