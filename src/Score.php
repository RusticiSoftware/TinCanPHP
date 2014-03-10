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

class Score implements VersionableInterface
{
    use ArraySetterTrait, FromJSONTrait, AsVersionTrait;

    protected $scaled;
    protected $raw;
    protected $min;
    protected $max;

    private static $directProps = array(
        'scaled',
        'raw',
        'min',
        'max',
    );

    public function __construct() {
        if (func_num_args() == 1) {
            $arg = func_get_arg(0);

            $this->_fromArray($arg);
        }
    }

    public function setScaled($value) { $this->scaled = $value; return $this; }
    public function getScaled() { return $this->scaled; }
    public function setRaw($value) { $this->raw = $value; return $this; }
    public function getRaw() { return $this->raw; }
    public function setMin($value) { $this->min = $value; return $this; }
    public function getMin() { return $this->min; }
    public function setMax($value) { $this->max = $value; return $this; }
    public function getMax() { return $this->max; }
}
