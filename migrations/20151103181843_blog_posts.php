<?php

use Phinx\Migration\AbstractMigration;

class BlogPosts extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    **/
    public function change()
    {
        $users = $this->table('posts', ['id' => false, 'primary_key' => ['uuid']]);
        $users->addColumn('uuid', 'uuid')
            ->addColumn('website_id', 'integer')
            ->addForeignKey('website_id', 'websites', 'id', array('delete'=> 'CASCADE', 'update'=> 'CASCADE'))
            ->addColumn('author_uuid', 'uuid', ['null'=>true])
            ->addColumn('url', 'string', ['limit'=>255])
            ->addColumn('title', 'string', ['limit'=>255])
            ->addColumn('subtitle', 'string', ['limit'=>255, 'null'=>true])
            ->addColumn('description', 'string', ['limit'=>255, 'null'=>true])
            ->addColumn('meta_description', 'string', ['limit'=>255, 'null'=>true])
            ->addColumn('categories', 'json', ['null'=>true])
            ->addColumn('tags', 'json', ['null'=>true])
            ->addColumn('keywords', 'json', ['null'=>true])
            ->addColumn('cover_picture', 'json', ['null'=>true]) // src, alt, title
            ->addColumn('content', 'json') // paragraphs => title, subtitle, content, image
            ->addColumn('published', 'timestamp', ['null'=>true])
            ->addColumn('created', 'timestamp')
            ->addColumn('updated', 'timestamp')
            ->create();
    }

    /**
     * Migrate Up.
     */
    public function up()
    {

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
