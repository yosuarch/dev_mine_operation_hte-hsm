<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralGender extends Migration
{

    // common info
    protected $table = 'general_gender';
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
            'gender' => [
                'type' => 'varchar',
                'constraint' => '12',
                'null' => false,
                'comment' => 'you know what to put on here',
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('gender', "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
