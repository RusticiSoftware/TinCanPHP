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

use InvalidArgumentException;

/**
 * An optional field that represents the outcome of a graded Activity achieved
 * by an Agent.
 */
class Score implements VersionableInterface
{
    use ArraySetterTrait, FromJSONTrait, AsVersionTrait;

    /**#@+
     * Class constants
     *
     * @var int
     */
    const DEFAULT_PRECISION = 2;
    const SCALE_MIN         = -1;
    const SCALE_MAX         = 1;
    /**#@- */

    /**
     * Decimal number between -1 and 1, inclusive
     *
     * @var float
     */
    protected $scaled;

    /**
     * Decimal number between min and max (if present, otherwise unrestricted),
     * inclusive
     *
     * @var float
     */
    protected $raw;

    /**
     * Decimal number less than max (if present)
     *
     * @var float
     */
    protected $min;

    /**
     * Decimal number greater than min (if present)
     *
     * @var float
     */
    protected $max;

    /**
     * Constructor
     *
     * @param float|array $aRawValue      the score value, may also be an array of properties
     * @param float       $aMin           the score minimum
     * @param float       $aMax           the score maximum
     * @param float       $aScalingFactor the score scaling factor
     */
    public function __construct($aRawValue = null, $aMin = null, $aMax = null, $aScalingFactor = null) {
        if (!is_array($aRawValue)) {
            $aRawValue = [
                'raw'    => $aRawValue,
                'min'    => $aMin,
                'max'    => $aMax,
                'scaled' => $aScalingFactor
            ];
        }
        $this->_fromArray($aRawValue);
    }

    /**
     * @param  float $aValue
     * @throws InvalidArgumentException
     * @return null
     */
    public function validate($aValue) {
        if (!isset($this->min, $this->max)) {
            return;
        }
        if ($aValue < $this->min || $aValue > $this->max) {
            throw new InvalidArgumentException(
                sprintf("Value must be between %s and %s", $this->min, $this->max)
            );
        }
    }

    /**
     * @param  int $aPrecision a rounding precision integer
     * @return null|float
     */
    public function getValue($aPrecision = self::DEFAULT_PRECISION) {
        if (!isset($this->raw)) {
            return null;
        }
        if (isset($this->scaled)) {
            return round($this->raw * $this->scaled, $aPrecision);
        }
        return round($this->raw, $aPrecision);
    }

    /**
     * @param  float $value
     * @throws InvalidArgumentException
     * @return self
     */
    public function setScaled($value) {
        if ($value < static::SCALE_MIN || $value > static::SCALE_MAX) {
            throw new InvalidArgumentException(sprintf(
                "Scale must be between %s and %s [%s]",
                static::SCALE_MIN,
                static::SCALE_MAX,
                $value
            ));
        }
        $this->scaled = (float) $value;
        return $this;
    }

    /**
     * @return null|float
     */
    public function getScaled() {
        return $this->scaled;
    }

    /**
     * @param  float $value
     * @return self
     */
    public function setRaw($value) {
        $this->validate($value);
        $this->raw = (float) $value;
        return $this;
    }

    /**
     * @return null|float
     */
    public function getRaw() {
        return $this->raw;
    }

    /**
     * @param  float $value
     * @throws InvalidArgumentException
     * @return self
     */
    public function setMin($value) {
        if (isset($this->max) && $value >= $this->max) {
            throw new InvalidArgumentException("Min must be less than max");
        }
        $this->min = (float) $value;
        return $this;
    }

    /**
     * @return null|float
     */
    public function getMin() {
        return $this->min;
    }

    /**
     * @param  float $value
     * @throws InvalidArgumentException
     * @return self
     */
    public function setMax($value) {
        if (isset($this->min) && $value <= $this->min) {
            throw new InvalidArgumentException("Max must be greater than min");
        }
        $this->max = (float) $value;
        return $this;
    }

    /**
     * @return null|float
     */
    public function getMax() {
        return $this->max;
    }
}
