<?php

namespace App-zero\Repository;

use App-zero\Model\WebsiteModel as Website;

class WebsiteRepository {

    function __construct($db) {
        $this->db = $db;
    }

    function getAllDomains() {
        $rows = $this->db->fetchAll('SELECT * FROM websites');
        $domains = array_map(
            function($row) { return $row['domain']; },
            $rows
        );
        return $domains;
    }

    function getAllWebsites() {
        $rows = $this->db->fetchAll('SELECT * FROM websites');
        $websites = [];
        foreach ($rows as $row) {
            array_push($websites, new Website($row));
        }
        return $websites;
    }

    function insert($website) {
        $now = date('Y-m-d H:i:s');
        $data = [
            'domain' => $website->domain,
            'plan' => '-',
            'content' => '{}',
            'backup' => '{}',
            'created'=>$now,
            'updated'=>$now
        ];
        return $this->db->insert('websites', $data);
    }

    function getByDomain($domain) {
        $rows = $this->db->fetchAll(
            'SELECT * FROM websites WHERE domain = ?',
            [$domain]
        );
        if (empty($rows)) {
            return null;
        } else {
            return new Website($rows[0]);
        }
    }

    function getUserWebsites($domains){
        $websites = [];
        foreach ($domains as $domain) {
            $website = $this->getByDomain($domain);
            array_push($websites, $website);
        };
        return $websites;
    }

    function update($website) {
        $backupWebsite = $this->getByDomain($website->domain);
        $now = date('Y-m-d H:i:s');
        return $this->db->executeUpdate(
            'UPDATE websites SET content = ?, backup = ?, updated = ? WHERE domain = ?',
            [json_encode($website->content), json_encode($backupWebsite->content), "now()", $website->domain]
        );
    }

}
