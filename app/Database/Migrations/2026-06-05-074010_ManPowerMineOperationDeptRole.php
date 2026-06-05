<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ManPowerMineOperationDeptRole extends Migration
{
    // common info
    protected $table = 'mp_mine_operation_dept_role';
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
            'job_title' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
            ],
            'role_class' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
            ],
        ]);


        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['job_title', 'role_class'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table, false, ['comment' => 'trying to follow the HCGA rola classification']);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
