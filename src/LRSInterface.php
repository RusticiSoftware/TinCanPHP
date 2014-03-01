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

interface LRSInterface {
    public function about();

    public function saveStatement($statement);
    public function saveStatements($statements);
    public function retrieveStatement($id);
    public function retrieveVoidedStatement($id);
    public function queryStatements($query);
    public function moreStatements($moreURL);

    public function retrieveStateKeys();
    public function retrieveState();
    public function saveState();
    public function deleteState();

    public function retrieveActivityProfileKeys();
    public function retrieveActivityProfile();
    public function saveActivityProfile();
    public function deleteActivityProfile();

    public function retrieveAgentProfileKeys();
    public function retrieveAgentProfile();
    public function saveAgentProfile();
    public function deleteAgentProfile();
}
