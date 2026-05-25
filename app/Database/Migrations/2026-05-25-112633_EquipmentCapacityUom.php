<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EquipmentCapacityUom extends Migration
{
    // common info
    protected $table = 'equipment_capacity_uom';
    protected $DBGroup = 'default';

    public function up()
    {
        // field's
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type' => 'varchar',
                'constraint' => 32,
                'null' => false,
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('code', "uq_" . $this->table . 'idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
