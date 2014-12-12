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

class TinCanAPI_Statement extends TinCanAPI_StatementBase
{
    protected $id;

    //
    // stored *must* store a string because DateTime doesn't
    // support sub-second precision, the setter will take a DateTime and convert
    // it to the proper ISO8601 representation, but if a user needs sub-second
    // precision as afforded by the spec they will have to create their own,
    // they can see TinCan\Util::getTimestamp for an example of how to do so
    //
    protected $stored;

    protected $authority;
    protected $version;
    protected $attachments;

    public static $directProps = array(
        'id',
        'timestamp',
        'stored',
        'version',
    );
    public static $versionedProps = array(
        'actor',
        'verb',
        'result',
        'context',
        'authority',
    );

    public function __construct() {
        call_user_func_array('parent::__construct', func_get_args());

        if (func_num_args() == 1) {
            $arg = func_get_arg(0);

            //
            // 'object' isn't in the list of properties so ._fromArray doesn't
            // pick it up correctly, but 'target' and 'object' shouldn't be in
            // the args at the same time, so handle 'object' here
            //
            if (isset($arg['object'])) {
                $this->setObject($arg['object']);
            }
        }
        if (! isset($this->attachments)) {
            $this->setAttachments(array());
        }
    }

    public function _asVersion(&$result, $version) {
	    //$result = $this->asVersion( $version );
        parent::_asVersion($result, $version);
	    $x = 'x';

        if (count($this->attachments) > 0) {
            $result['attachments'] = array();

            foreach ($this->attachments as $k) {
                array_push($result['attachments'], $k->asVersion($version));
            }
        }
    }

	//public function asVersion( $version ) {
	//	$result = parent::asVersion( $version );
	//	$this->_asVersion($result, $version);
	//	return $result;
	//}

    public function stamp() {
        $this->setId(Util::getUUID());
        $this->setTimestamp(Util::getTimestamp());

        return $this;
    }

    public function setId($value) {
        if (isset($value) && ! preg_match(TinCanAPI_Util::UUID_REGEX, $value)) {
            throw new InvalidArgumentException('arg1 must be a UUID "' . $value . '"');
        }
        $this->id = $value;
        return $this;
    }
    public function getId() { return $this->id; }
    public function hasId() { return isset($this->id); }

    public function setStored($value) {
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

        $this->stored = $value;

        return $this;
    }
    public function getStored() { return $this->stored; }

    public function setAuthority($value) {
        if (! $value instanceof TinCanAPI_Agent && is_array($value)) {
            $value = new TinCanAPI_Agent($value);
        }

        $this->authority = $value;

        return $this;
    }
    public function getAuthority() { return $this->authority; }

    public function setVersion($value) { $this->version = $value; return $this; }
    public function getVersion() { return $this->version; }

    public function setAttachments($value) {
        foreach ($value as $k => $v) {
            if (! $value[$k] instanceof TinCanAPI_Attachment) {
                $value[$k] = new TinCanAPI_Attachment($value[$k]);
            }
        }

        $this->attachments = $value;

        return $this;
    }
    public function getAttachments() { return $this->attachments; }
    public function addAttachment($value) {
        if (! $value instanceof TinCanAPI_Attachment) {
            $value = new TinCanAPI_Attachment($value);
        }

        array_push($this->attachments, $value);

        return $this;
    }
}
