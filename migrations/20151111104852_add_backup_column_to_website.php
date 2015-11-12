<?php

use Phinx\Migration\AbstractMigration;

class AddBackupColumnToWebsite extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     */
    public function change()
    {
        $websites = $this->table('websites');
        $websites->addColumn('backup', 'json', array('after' => 'content', 'null'=>true))
                 ->update();
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