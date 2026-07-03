<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiSpoting extends Migration
{

    // common info
    protected $table = 'psi_observed_item';
    protected $DBGroup = 'appsDBGroup';

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
            'checking_part' => [
                'type' => 'varchar',
                'constraint' => 32,
                'null' => false,
            ],
            'spot' => [
                'type' => 'int',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
            ],
            'danger_tag' => [
                'type' => 'int',
                'constraint' => 1,
                'null' => false,
            ],
            'equipment_type' => [
                'type' => 'int',
                'constraint' => 2,
                'null' => false,
            ],
            'description' => [
                'type' => 'text',
                'null' => false,
                'default' => 'place the desciption on here',
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['checking_part', 'danger_tag'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
