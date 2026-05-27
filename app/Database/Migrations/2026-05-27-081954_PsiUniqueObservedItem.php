<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiUniqueObservedItem extends Migration
{

    // common info
    protected $table = 'psi_unique_observed_item';
    protected $DBGroup = 'default';

    public function up()
    {
        // create 
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'checking_part' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
                'comment' => 'this field is base on the existing and unique value of P2H sheet, in the future maybe it will be change',
            ],
            'checking_part_idn' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => false,
                'comment' => 'this field is base on the existing and unique value of P2H sheet but in indonesian',
            ],
            'spot' => [
                'type' => 'int',
                'constraint' => 2,
                'null' => false,
                'comment' => 'refrencing to psi_spoting_position table'
            ],
            'danger_tag' => [
                'type' => 'int',
                'constraint' => 2,
                'null' => false,
                'comment' => 'refrencing to ohse_danger_tag_code table',
            ],
        ]);


        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('checking_part', "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
