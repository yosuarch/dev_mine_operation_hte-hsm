<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MaterialCategory extends Migration
{

    protected $table   = 'material_category';
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
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
                'comment' => 'this field is all the material list, it should be update by Mine Plan Engineer in the future'
            ],
            'description' => [
                'type' => 'text',
                'null' => false,
                'default' => '0',
                'comment' => 'this is the material description',
            ],
        ]);
        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable($this->table, true, [
            'comment' => 'known material category'
        ]);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table, true);
    }
}
