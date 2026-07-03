<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EquipmentRegister extends Migration
{
    // common info
    protected $table = 'equipment_register';
    protected $DBGroup = 'appsDBGroup';

    public function up()
    {
        // generate table
        $this->forge->addField([

            'idx' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'num_code' => [
                'type' => 'varchar',
                'constraint' => 6,
                'null' => false,
                'comment' => 'only contain the equipment id number',
            ],
            'text_code' => [
                'type' => 'varchar',
                'constraint' => 6,
                'null' => false,
                'comment' => 'only contain the equipment id text',
            ],
            'model' => [
                'type' => 'int',
                'constraint' => 2,
                'null' => false,
            ],
            'class' => [
                'type' => 'decimal',
                'constraint' => '11,2',
                'null' => false,
            ],
            'class_uom' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => false,
            ],
            'capacity' => [
                'type' => 'decimal',
                'constraint' => '11,2',
                'null' => false,
            ],
            'capacity_uom' => [
                'type' => 'int',
                'constraint' => 2,
                'null' => false,
            ],
            'capacity_form' => [
                'type' => 'int',
                'constraint' => 2,
                'null' => false,
            ],
            'attachment' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => true,
            ],
            'onsite_date' => [
                'type' => 'date',
            ],
            'created_at' => [
                'type' => 'datetime',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('current_timestamp'),
            ],
            'modified_at' => [
                'type' => 'datetime',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
            ],
            'deleted_at' => [
                'type' => 'datetime',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['num_code', 'text_code', 'model', 'capacity'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
