<?php

use Phinx\Migration\AbstractMigration;

class TestMigration extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        // create the table
        $table = $this->table('phinx_test_table');
        $table->addColumn('int', 'integer')
              ->addColumn('text', 'string', ['limit' => 30])
              ->addColumn('created', 'datetime')
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
