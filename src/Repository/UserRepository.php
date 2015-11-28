<?php

namespace Appzero\Repository;

use Appzero\Model\UserModel as User;

class UserRepository extends Repository{

    function __construct($db) {
        $this->db = $db;
    }

    function insert($agent) {
        $now = new \DateTime();
        $data = [
            'uuid' => $this->generateUuid(),
            'first_name' => $agent->first_name,
            'last_name' => $agent->last_name,
            'username' => $agent->username,
            'password' => $agent->password,
            'role' => $agent->role,
            'created' => $now->format('c'),
            'updated' => $now->format('c'),
            'active' => true
        ];

        try {$result = $this->db->insert('users', $data);} catch(\Exception $e) {
            return trigger_error($e->getMessage(), E_USER_ERROR);
        }
        return json_encode($data['uuid']);
    }

    function getbyUuid($uuid) {
        $rows = $this->db->fetchAll(
            'SELECT * FROM users WHERE uuid = ?',
            [$uuid]
        );
        return new User($rows[0]);
    }

    function getAll(){
         $rows = $this->db->fetchAll('SELECT * FROM users');
         $users = [];
         for($i = 0; $i < count($rows); $i++){
             $user = new User($rows[$i]);
             array_push($users, $user);
         };
         return $users;
    }

    function getByUsername($username) {
        $rows = $this->db->fetchAll(
            'SELECT * FROM users WHERE username = ?',
            [$username]
        );
        return new User($rows[0]);
    }

    function isUsernameTaken($username) {
        $rows = $this->db->fetchAll('SELECT username FROM users');
        $usernames =[];
        foreach ($rows as $row) {
            $row = implode($row);
            array_push($usernames, $row);
        }
        if(in_array($username, $usernames)){
            return true;
        }
        return false;
    }

    function getUsersForSilex() {
        $users = $this->db->fetchAll('SELECT username, password, role FROM users');
        $usernamesAndEncodedPasswords =[];
        foreach ($users as $user) {
            $newUser = [$user['role'], $user['password']];
            $usernamesAndEncodedPasswords[$user['username']] = $newUser;
        }
        return $usernamesAndEncodedPasswords;
    }

    function update($post){
        $qb = $this->db->createQueryBuilder();
        $query = $qb->update('users')
                ->set('first_name', $qb->expr()->literal($post->first_name))
                ->set('last_name', $qb->expr()->literal($post->last_name))
                ->set('username', $qb->expr()->literal($post->username))
                ->set('password', $qb->expr()->literal($post->password))
                ->where('uuid = :uuid')
                ->setParameter('uuid', $post->uuid);
        try {$result = $query->execute();}catch(\Exception $e) {
            return trigger_error($e->getMessage(), E_USER_ERROR);
        }
        return 'The user was updated successfully.';
    }

    function activate($post){
        $qb = $this->db->createQueryBuilder();
        $query = $qb->update('users')
                ->set('active', $post['state'])
                ->where('uuid = :uuid')
                ->setParameter('uuid', $post['uuid']);
        try {$result = $query->execute();}catch(\Exception $e) {
            return trigger_error($e->getMessage(), E_USER_ERROR);
        }
        return 'The user was updated successfully.';
    }
}
