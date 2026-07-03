<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralJobStatus extends Migration
{

    // common info
    protected $table = 'general_job_status';
    protected $DBGroup = 'appsDBGroup';

    public function up()
    {
        // create table
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
                'comment' => 'first stage is use OPEN, CLOSE and CONTINUE'
            ]
        ]);


        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['idx'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
