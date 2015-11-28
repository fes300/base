<?php

use Phinx\Migration\AbstractMigration;

class Users extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     **/
    public function change()
    {
        $users = $this->table('users', array('id' => false, 'primary_key' => array('uuid')));
        $users->addColumn('uuid', 'uuid')
            ->addColumn('first_name', 'string', array('limit'=>255, 'null'=>true))
            ->addColumn('last_name', 'string', array('limit'=>255, 'null'=>true))
            ->addColumn('username', 'string', array('limit'=>255))
            ->addColumn('password', 'string', array('limit'=>255))
            ->addColumn('role', 'string', array('limit'=>255))
            ->addColumn('active', 'boolean')
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
