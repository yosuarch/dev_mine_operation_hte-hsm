<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EquipmentUniqueType extends Migration
{

    // common info
    protected $table = 'equipment_unique_type';
    protected $DBGroup = 'default';

    public function up()
    {
        //

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey('checking_part', "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        //
    }
}
