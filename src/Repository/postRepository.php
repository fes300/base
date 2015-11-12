<?php

namespace App-zero\Repository;

use App-zero\Model\PostModel as Post;

class PostRepository {

    function __construct($db) {
        $this->db = $db;
    }

    function getAllPosts() {
        $rows = $this->db->fetchAll('SELECT * FROM posts');
        $posts = [];
        foreach ($rows as $row) {
            array_push($posts, new Website($row));
        }
        return $posts;
    }

    function getByDomain($domain, $filters = null) {
        $rows = $this->db->fetchAll('SELECT posts.*
            FROM posts
            JOIN websites ON websites.id = posts.website_id
            WHERE websites.domain = ?',
            [$domain]
        );
        $posts = [];
        foreach ($rows as $row) {
            array_push($posts, new Website($row));
        }
        return $posts;
    }

    function getByDomainAndUrl($domain, $url) {
        $posts = $this->db->fetchAll('SELECT posts.*
            FROM posts
            JOIN websites ON websites.id = posts.website_id
            WHERE websites.domain = ?
            AND posts.url = ?',
            [$domain, $url]
        );
        return $posts[0];
    }

    function insert($post) {
        $data = [
            'uuid' => $this->generateUuid(),
            'website_id' => $post->websiteId,
            'author_uuid' => $post->authorUuid,
            'url' => $post->url,
            'title' => $post->title,
            'subtitle' => $post->subtitle,
            'description' => $post->description,
            'meta_description' => $post->metaDescription,
            'categories' => json_encode($post->categories),
            'tags' => json_encode($post->tags),
            'keywords' => json_encode($post->keywords),
            'cover_picture' => json_encode($post->coverPicture),
            'content' => json_encode($post->content),
            'published' => $post->published,
            'created'=>'now()',
            'updated'=>'now()'
        ];
        return $this->db->insert('posts', $data);
    }

    function update($post) {
        $data = [
            'author_uuid' => $post->authorUuid,
            'url' => $post->url,
            'title' => $post->title,
            'subtitle' => $post->subtitle,
            'description' => $post->description,
            'meta_description' => $post->metaDescription,
            'categories' => json_encode($post->categories),
            'tags' => json_encode($post->tags),
            'keywords' => json_encode($post->keywords),
            'cover_picture' => json_encode($post->coverPicture),
            'content' => json_encode($post->content),
            'published' => $post->published,
            'updated'=>'now()',
            'uuid' => $this->generateUuid(),
        ];
        return $this->db->executeUpdate(
            'UPDATE posts
            SET author_uuid = ?, url = ?, title = ?, subtitle = ?, description = ?, meta_description = ?, categories = ?, tags = ?, keywords = ?, cover_picture = ?, content = ?, published = ?, updated = ?
            WHERE uuid = ?',
            $data
        );
    }

}
