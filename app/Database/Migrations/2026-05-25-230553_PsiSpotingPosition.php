<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiSpotingPosition extends Migration
{

    // common info
    protected $table = 'psi_spoting_position';
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
                'constraint' => 32,
                'null' => false,
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
