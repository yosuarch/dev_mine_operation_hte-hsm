<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EquipmentUniqueType extends Migration
{

    // common info
    protected $table = 'equipment_unique_type';
    protected $DBGroup = 'appsDBGroup';

    public function up()
    {
        // craete table
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
                'comment' => 'unique equipment type',
            ],
            'abbreviation' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => true,
                'comment' => 'the abbreviation of the type',
            ],
        ]);


        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('code', "uq_" . $this->table . '_for_idx');

        // create the table
        $this->forge->createTable($this->table, true);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
