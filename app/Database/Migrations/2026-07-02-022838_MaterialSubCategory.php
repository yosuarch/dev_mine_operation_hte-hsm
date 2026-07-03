<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MaterialSubCategory extends Migration
{
    protected $table   = 'material_sub_category';
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
            'material' => [
                'type' => 'int',
                'constraint' => 4,
                'null' => false,
                'comment' => 'this filed is refrencing the material_category tables',
            ],
            'sub_material' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
                'comment' => 'this the list of sub material, it can grow base on the needs',
            ],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable($this->table, true, [
            'comment' => 'known sub_material category'
        ]);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table, true);
    }
}
