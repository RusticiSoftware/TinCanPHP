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

class LanguageMap extends Map
{
    public function getNegotiatedLanguageString ($acceptLanguage = null) {
        $negotiator = new \Negotiation\Negotiator();
        if ($acceptLanguage === null) {
            $acceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE']. ', *' : '*';
        }
        $availableLanguages = array_keys ($this->_map);
        $preferredLanguage = $negotiator->getBest($acceptLanguage, $availableLanguages);

        return $this->_map[$preferredLanguage->getValue()];
    }
}
