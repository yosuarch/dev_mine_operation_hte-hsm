<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ManPowerSubDept extends Migration
{
    // common info
    protected $table = 'mp_sub_dept';
    protected $DBGroup = 'default';

    public function up()
    {
        // create the field
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 6,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
            ],
        ]);


        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['code'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table, false, ['comment' => '20260605 it is still following the Mining Departement Sub-Section Structure, if needed it will be populated by another departemen sub-section']);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
