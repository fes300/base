<?php

namespace App-zero\Model;

class UserModel {

    function __construct($data) {
        $this->uuid = empty($data['uuid']) ? '' : $data['uuid'];
        $this->first_name = empty($data['first_name']) ? null : $data['first_name'];
        $this->last_name = empty($data['last_name']) ? null : $data['last_name'];
        $this->company = empty($data['company']) ? null : $data['company'];
        $this->domains = empty($data['domains']) ? null : json_decode($data['domains']);
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->role = $data['role'];
        $this->active = empty($data['active']) ? null : $data['active'];
        $this->created = empty($data['created']) ? null : $data['created'];
        $this->updated = empty($data['updated']) ? null : $data['updated'];
    }
}
