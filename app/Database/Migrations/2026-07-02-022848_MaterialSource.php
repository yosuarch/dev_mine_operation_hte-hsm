<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MaterialSource extends Migration
{
    protected $table   = 'material_source';
    protected $DBGroup = 'appsDBGroup';

    public function up()
    {
        // add table fields
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'auto_increment' => true,
                'comment' => 'this is primary key',
            ],
            'source' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
                'comment' => 'known material sources',
            ],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable($this->table, true, [
            'comment' => 'known material sources'
        ]);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table, true);
    }
}
