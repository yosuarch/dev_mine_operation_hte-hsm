<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ManPowerName extends Migration
{
    // common info
    protected $table = 'mp_list';
    protected $DBGroup = 'default';

    public function up()
    {
        // create the table
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'varchar',
                'constraint' => 128,
                'null' => false,
            ],
            'gender' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
            ],
            'employee_id' => [
                'type' => 'int',
                'constraint' => 16,
                'null' => false,
            ],
            'role' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => false,
                'default' => 999,
            ],
            'sub_departement' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => false,
                'default' => 999,
            ],
            'first_phone_number' => [
                'type' => 'varchar',
                'constraint' => 32,
                'null' => true,
            ],
            'second_phone_number' => [
                'type' => 'varchar',
                'constraint' => 32,
                'null' => true,
            ],
            'emergency_contact_number' => [
                'type' => 'varchar',
                'constraint' => 32,
                'null' => true,
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['name', 'employee_id', 'gender'], "uq_" . $this->table . '_idx');
        $this->forge->addUniqueKey('employee_id', 'uq_mp_list_emp-id_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
