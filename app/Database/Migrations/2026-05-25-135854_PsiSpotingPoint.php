<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiSpotingPoint extends Migration // PSI is stand for Pre-Start Inspection
{

    // common info
    protected $table = 'psi_spot_point';
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
            'code' => [
                'type' => 'varchar',
                'constraint' => 6,
                'null' => false,
                'comment' => 'this is the spoting code',
            ],
            'description' => [
                'type' => 'text',
                'null' => false,
                'comment' => 'this is the spoting code description, perhaps it will only be two-part such as in/outside of cabin',
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
