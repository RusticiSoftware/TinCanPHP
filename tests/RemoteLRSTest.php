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

use TinCan\RemoteLRS;

class RemoteLRSTest extends PHPUnit_Framework_TestCase {
    public function testInstantiation() {
        $lrs = new RemoteLRS();
        $this->assertInstanceOf('TinCan\RemoteLRS', $lrs);
        $this->assertAttributeEmpty('endpoint', $lrs, 'endpoint empty');
        $this->assertAttributeEmpty('auth', $lrs, 'auth empty');
        $this->assertAttributeEmpty('extended', $lrs, 'extended empty');
        $this->assertSame(TinCan\Version::latest(), $lrs->getVersion(), 'version set to latest');
    }

    public function testAbout() {
        $lrs = new RemoteLRS('http://cloud.scorm.com/tc/3HYPTQLAI9/sandbox', '1.0.0', 'Basic RkFDTjkyYlNiVjVMaVUwdzFmTTphc0dHVjNUUFVDa01ZOXU5a1Nr');
        //$result = $lrs->about();
        //print $result['content'];
        //print var_dump($lrs->about());
    }

    public function testSaveStatement() {
        $lrs = new RemoteLRS('http://cloud.scorm.com/tc/3HYPTQLAI9/sandbox', '1.0.0', 'Basic RkFDTjkyYlNiVjVMaVUwdzFmTTphc0dHVjNUUFVDa01ZOXU5a1Nr');
        $statement = new TinCan\Statement(
            [
                'actor' => [
                    'mbox' => COMMON_MBOX
                ],
                'verb' => [
                    'id' => COMMON_VERB_ID
                ],
                'object' => new TinCan\Activity([
                    'id' => COMMON_ACTIVITY_ID
                ])
            ]
        );
        $statement->stamp();

        $resp = $lrs->saveStatement($statement);
        print "Status: " . $resp['metadata']['wrapper_data'][0] . "\n";
        print "Content: " . $resp['content'] . "\n";
    }
}
