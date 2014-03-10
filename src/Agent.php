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

class Agent implements VersionableInterface, StatementTargetInterface
{
    use ArraySetterTrait, FromJSONTrait;
    protected $objectType = 'Agent';

    protected $name;
    protected $mbox;
    protected $mbox_sha1sum;
    protected $openid;
    protected $account;

    public function __construct() {
        if (func_num_args() == 1) {
            $arg = func_get_arg(0);

            $this->_fromArray($arg);
        }
    }

    public function asVersion($version) {
        $result = array(
            'objectType' => $this->objectType
        );

        if (isset($this->name)) {
            $result['name'] = $this->name;
        }

        //
        // only one of these
        //
        if (isset($this->account)) {
            $result['account'] = $this->account->asVersion($version);
        }
        elseif (isset($this->mbox_sha1sum)) {
            $result['mbox_sha1sum'] = $this->mbox_sha1sum;
        }
        elseif (isset($this->mbox)) {
            $result['mbox'] = $this->mbox;
        }
        elseif (isset($this->openid)) {
            $result['openid'] = $this->openid;
        }

        return $result;
    }

    public function getObjectType() { return $this->objectType; }

    public function setName($value) { $this->name = $value; return $this; }
    public function getName() { return $this->name; }

    public function setMbox($value) {
        if (isset($value) && (! (stripos($value, 'mailto:') === 0))) {
            $value = 'mailto:' . $value;
        }
        $this->mbox = $value;
        return $this;
    }
    public function getMbox() { return $this->mbox; }

    public function setMbox_sha1sum($value) { $this->mbox_sha1sum = $value; return $this; }
    public function getMbox_sha1sum() { return $this->mbox_sha1sum; }
    public function setOpenid($value) { $this->openid = $value; return $this; }
    public function getOpenid() { return $this->openid; }

    public function setAccount($value) {
        if (! $value instanceof AgentAccount && is_array($value)) {
            $value = new AgentAccount($value);
        }

        $this->account = $value;

        return $this;
    }
    public function getAccount() { return $this->account; }
}
