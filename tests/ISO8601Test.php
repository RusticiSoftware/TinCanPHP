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

use TinCan\Statement;
use TinCan\State;

class ISO8601Test extends PHPUnit_Framework_TestCase {
    public function testProperties() {
        $str_datetime = '2014-12-15T19:16:05+00:00';
        $str_datetime_tz = '2014-12-15T13:16:05-06:00';

        $datetime = new \DateTime();
        $datetime->setDate(2014, 12, 15);
        $datetime->setTime(19, 16, 05);

        $statement = new Statement();
        $statement->setStored($datetime);
        $statement->setTimestamp($datetime);

        $document = new State();
        $document->setTimestamp($datetime);

        $this->assertEquals($statement->getStored(), $str_datetime, 'stored matches');
        $this->assertEquals($statement->getTimestamp(), $str_datetime, 'timestamp matches');
        $this->assertEquals($document->getTimestamp(), $str_datetime, 'document timestamp matches');

        $datetime->setTimezone(new DateTimeZone('America/Chicago'));
        $statement->setStored($datetime);
        $statement->setTimestamp($datetime);
        $document->setTimestamp($datetime);

        $this->assertEquals($statement->getStored(), $str_datetime_tz, 'stored matches');
        $this->assertEquals($statement->getTimestamp(), $str_datetime_tz, 'timestamp matches');
        $this->assertEquals($document->getTimestamp(), $str_datetime_tz, 'document timestamp matches');
    }
}
