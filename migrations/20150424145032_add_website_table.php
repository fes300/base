<?php

use Phinx\Migration\AbstractMigration;

class AddWebsiteTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('websites');
        $table->addColumn('domain', 'string')
            ->addColumn('plan', 'string')
            ->addColumn('content', 'json')
            ->addColumn('created', 'datetime')
            ->addColumn('updated', 'datetime')
            ->create();
    }
}
