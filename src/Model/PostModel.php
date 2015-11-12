<?php

namespace App-zero\Model;

class PostModel {

    function __construct($data) {
        $this->uuid = $data['uuid'];
        $this->websiteId = $data['website_id'];
        $this->author_uuid = $data['author_uuid'];
        $this->url = $data['url'];
        $this->title = $data['title'];
        $this->subtitle = $data['subtitle'];
        $this->description = $data['description'];
        $this->metaDescription = $data['meta_description'];
        $this->categories = json_decode($data['categories']);
        $this->tags = json_decode($data['tags']);
        $this->keywords = json_decode($data['keywords']);
        $this->coverPicture = json_decode($data['cover_picture']); // src, alt, title
        $this->content = json_decode($data['content']); // paragraphs => title, subtitle, content, image
        $this->published = empty($data['published']) ? null : strtotime($data['published']);
        $this->created = strtotime($data['created']);
        $this->updated = strtotime($data['updated']);
    }

}
