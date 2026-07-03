<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WorkingShift extends Migration
{

    // common info
    protected $table = 'general_working_shift';
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
                'comment' => 'working shift',
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
