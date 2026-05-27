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
            'employee_id' => [
                'type' => 'varchar',
                'constraint' => 16,
                'null' => false,
            ],
            'gender' => [
                'type' => 'int',
                'unsigned' => true,
                'null' => false,
            ],
            'first_phone_number' => [],
            'second_phone_number' => [],
            'emergency_contact_number' => [],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['employee_id', 'gender'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
