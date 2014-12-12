<?php

/**
 * TinCanAPI_Object which all other objects derive from.
 *
 * Using a base object as PHP 5.2 does not support traits.
 * All classes have also been prefixed with TinCanAPI as PHP 5.2 does
 * not support namespaces.
 */
class TinCanAPI_Object {

    public static function fromJSON($jsonStr) {
        //
        // 2nd arg as true means return value is an assoc. array rather than object
        //
        $cfg = json_decode($jsonStr, true);

        if (is_null($cfg)) {
            // $err = json_last_error(); // not in PHP 5.2
			$err = "null";
            throw new InvalidArgumentException("Invalid JSON: $err");
        }
        $called_class = self::get_called_class();
        return new $called_class($cfg);
    }

    public function asVersion($version) {
        $result = array();

        $klass = get_class($this);
        if (property_exists($klass, 'directProps')) {
            foreach ($klass::$directProps as $key) {
                //print "AsVersionTrait::asVersion - " . get_class($this) . " - $key:" . $this->$key . "\n";

                if (isset($this->$key) && ((! is_array($this->$key)) || (count($this->$key) > 0))) {
                    $result[$key] = $this->$key;
                }
            }
        }
        if (property_exists($klass, 'versionedProps')) {
            foreach ($klass::$versionedProps as $key) {
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

    public function _fromArray($options) {
        foreach (get_object_vars($this) as $k => $v) {
            $method = 'set' . ucfirst($k);
            if (isset($options[$k]) && method_exists($this, $method)) {
                $this->$method($options[$k]);
            }
        }
    }
	
	function random_pseudo_bytes( $length ) {

	    $seed = (double)microtime()*1000003;
	    if (function_exists('getmypid')) {
            $seed += getmypid();	    	
	    }

	    mt_srand($seed);

	    $output = '';
		
	    for ($i = 0; $i < $length; ++$i) {
	        $output .= chr(mt_rand(0, 255));	    	
	    }

	    return $output;
	}
	
	// Static method for TinCanAPI_Object. Can override this elsewhere.
	public static function get_called_class() {
		$objects = array();
		$traces = debug_backtrace();
		foreach ($traces as $trace) {
			if (isset($trace['object'])) {
				if (is_object($trace['object'])) {
					$objects[] = $trace['object'];
				}
			}
		}
	
		if (count($objects))
		{
			return get_class($objects[0]);
		}
	}
	
	
}