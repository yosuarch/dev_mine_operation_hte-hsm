<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DumptruckActivityRecord extends Migration
{

    protected $table   = 'dumptruck_activity_record';
    protected $DBGroup = 'appsDBGroup';

    public function up()
    {
        // add field
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => '7',
                'unsigned' => true,
                'auto_increment' => true,
                'comment' => 'Primary KEY of everything',
            ],
            'date' => [
                'type' => 'date',
                'null' => false,
                'default' =>  new \CodeIgniter\Database\RawSql('current_date'),
                'comment' => 'this is the defined date by driver',
            ],
            'shift' => [
                'type' => 'tinyint',
                'constraint' => '1',
                'null' => false,
                'default' => 1,
            ],
            'time' => [
                'type' => 'time',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('current_date'),
                'comment' => 'it will take the current time when the data INSERTED',
            ],
            'loader_id' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => true,
                'comment' => 'this will link with excavator_activity table, also we can leave it blank because sometimes DT-Driver can get the loader-id',
            ],
            'loader_opr_id' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => true,
                'comment' => 'this will link with excavator_activity table, also we can leave it blank because sometimes DT-Driver can get the operator-name',
            ],
            'hauler_id' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
            ],
            'hauler_drvr_id' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
            ],
            'loading_area' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
            ],
            'loading_area_note' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => true,
            ],
            'dumping_area' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
            ],
            'dumping_area_note' => [
                'type' => 'varchar',
                'constraint' => 64,
                'null' => true,
            ],
            'material_category' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
            ],
            'material_sub_category' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
            ],
            'material_note_by_driver' => [
                'type' => 'text',
                'null' => true,
            ],
            'driver_note' => [
                'type' => 'text',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'datetime',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('current_timestamp'),
            ],
            'modified_at' => [
                'type' => 'datetime',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
            ],
            'deleted_at' => [
                'type' => 'datetime',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
            ],
        ]);

        // --- add primary key
        $this->forge->addPrimaryKey('idx');

        // --- create the table
        $this->forge->createTable($this->table, true, [
            'comment' => 'this is only the dumptruck activity, hope it can cover all the activity of dump-truck'
        ]);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table, true);
    }
}
