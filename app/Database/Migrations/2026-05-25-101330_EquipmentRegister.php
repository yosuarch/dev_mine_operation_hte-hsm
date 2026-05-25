<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EquipmentRegister extends Migration
{
    // common info
    protected $table = 'equipment_register';
    protected $DBGroup = 'default';

    public function up()
    {
        // generate table
        // $this->forge->addField([
        //     'idx' => [
        //         'idx' => [
        //             'type' => 'int',
        //             'constraint' => 3,
        //             'unsigned' => true,
        //             'auto_increment' => true,
        //         ],
        //         'code' => [],
        //     ],
        // ]);
    }

    public function down()
    {
        //
    }
}
