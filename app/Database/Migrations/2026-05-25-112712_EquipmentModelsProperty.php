<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EquipmentModelsProperty extends Migration
{
    // common info
    protected $table = 'equipment_models_property';
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
            'model' => [
                'type' => 'varchar',
                'constraint' => 32,
                'null' => false,
            ],
            'type' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
            ],
            'brand' => [
                'type' => 'varchar',
                'constraint' => 32,
                'null' => false,
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('model', "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
