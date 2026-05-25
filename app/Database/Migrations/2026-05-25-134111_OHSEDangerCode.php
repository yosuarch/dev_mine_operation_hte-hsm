<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OHSEDangerCode extends Migration
{

    // common info
    protected $table = 'ohse_danger_code';
    protected $DBGroup = 'default';

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
            'code' => [
                'type' => 'varchar',
                'constraint' => 5,
                'null' => false,
            ],
            'description' => [
                'type' => 'text',
                'null' => false,
                'comment' => 'put the OHSE danger code in this column. the description is MUST FOLLOW what OHSE says',
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('code', "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
