<?php

namespace App-zero\Model;

class WebsiteModel {

    function __construct($data) {
        $this->domain = $data['domain'];
        $this->content = json_decode($data['content']);
        $this->backup = json_decode($data['backup']);
        $this->created = strtotime($data['created']);
        $this->updated = strtotime($data['updated']);
    }

    function getDraftNames() {
        $draftNames = [];
        if (!empty($this->content->drafts)) {
            foreach ($this->content->drafts as $key => $value) {
                array_push($draftNames, $key);
            }
        }
        return $draftNames;
    }

    function getPageNames() {
        $pageNames = [];
        if (!empty($this->content->pages)) {
            foreach ($this->content->pages as $key => $value) {
                array_push($pageNames, $key);
            }
        }
        return $pageNames;
    }

}
