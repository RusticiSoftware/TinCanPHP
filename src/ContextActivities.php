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

class ContextActivities implements VersionableInterface {
    use ArraySetterTrait, FromJSONTrait;

    protected $category;
    protected $parent;
    protected $grouping;
    protected $other;

    static private $directProps = array(
        'category',
        'parent',
        'grouping',
        'other',
    );

    public function __construct() {
        //
        // TODO: need to handle the single or multiple handling, need to detect
        //       based on existence of 'objectType' property I suspect, unless
        //       instanceof on Activity will do better
        //
        if (func_num_args() == 1) {
            $arg = func_get_arg(0);

            $this->_fromArray($arg);
        }

        foreach (
            [
                'category',
                'parent',
                'grouping',
                'other',
            ] as $k
        ) {
            $method = 'set' . ucfirst($k);

            if (! isset($this->$k)) {
                $this->$method(array());
            }
        }
    }

    public function asVersion($version) {
        $result = array();

        foreach (self::$directProps as $k) {
            if (isset($this->$k) && count($this->$k) > 0) {
                print "$k has activities\n";
                if (! isset($result[$k])) {
                    $result[$k] = array();
                }
                $inner = $this->$k;
                foreach ($inner as $act) {
                    array_push($result[$k], $act->asVersion($version));
                }
            }
        }
        return $result;
    }

    public function setCategory($value) { $this->category = $value; return $this; }
    public function getCategory() { return $this->category; }
    public function setParent($value) { $this->parent = $value; return $this; }
    public function getParent() { return $this->parent; }
    public function setGrouping($value) { $this->grouping = $value; return $this; }
    public function getGrouping() { return $this->grouping; }
    public function setOther($value) { $this->other = $value; return $this; }
    public function getOther() { return $this->other; }
}
