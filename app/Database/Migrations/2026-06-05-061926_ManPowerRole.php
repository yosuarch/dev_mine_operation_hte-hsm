<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ManPowerRole extends Migration
{

    // common info
    protected $table = 'mp_role';
    protected $DBGroup = 'appsDBGroup';

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
            'role' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
            ],
            'role_class' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
            ],
            'abbreviation' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
            ],
        ]);


        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['role', 'role_class', 'abbreviation'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table, false, ['comment' => 'trying to follow the HCGA rola classification']);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
