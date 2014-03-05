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

trait AsVersionTrait {
    public function asVersion($version) {
        $result = array();

        if (property_exists(get_class($this), 'directProps')) {
            foreach (self::$directProps as $key) {
                // TODO: should this be a prop name -> method map instead?
                if (isset($this->$key) && ((! is_array($this->$key)) || (count($this->$key) > 0))) {
                    $result[$key] = $this->$key;
                }
            }
        }
        if (property_exists(get_class($this), 'versionedProps')) {
            foreach (self::$versionedProps as $key) {
                if (isset($this->$key)) {
                    //print "AsVersionTrait::asVersion: " . get_class($this) . " - $key\n";
                    $versioned = $this->$key->asVersion($version);
                    if (isset($versioned)) {
                        $result[$key] = $versioned;
                    }
                }
            }
        }

        if (method_exists($this, '_asVersion')) {
            $this->_asVersion($result, $version);
        }

        return $result;
    }
}
