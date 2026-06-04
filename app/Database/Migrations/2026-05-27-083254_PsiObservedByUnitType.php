<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiObservedByUnitType extends Migration
{

    // common info
    protected $table = 'psi_observed_by_unit_type';
    protected $DBGroup = 'default';

    public function up()
    {
        // create the table
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'checking_part' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => false,
                'comment' => 'refrencing to psi_unique_observed_item table',
            ],
            'spot' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => false,
                'comment' => 'refrencing to psi_spoting_position table',
            ],
            'danger_tag' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => false,
                'comment' => 'refrencing to ohse_danger_tag_code table',
            ],
            'equipment_type' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => false,
                'comment' => 'refrencing to equipment_unique_type table',
            ],
        ]);
        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['checking_part', 'spot', 'danger_tag', 'equipment_type'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
