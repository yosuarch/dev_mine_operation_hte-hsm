<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EquipmentWorkingHour extends Migration
{
    // common info
    protected $table = 'wh_recording';
    protected $DBGroup = 'default';

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
            'equipment_id' => [
                'type' => 'int',
                'constraint' => 11,
                'null' => false,
            ],
            'date' => [
                'type' => 'date',
                'null' => false,
            ],
            'shift' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
            ],
            'operator_name' => [
                'type' => 'int',
                'constraint' => 5,
                'unsigned' => true,
                'null' => false,
                'comment' => 'it will refrence the manpower table',
            ],
            'hourmeter_start' => [
                'type' => 'decimal',
                'constraint' => '18,2',
                'null' => true,
            ],
            'hourmeter_end' => [
                'type' => 'decimal',
                'constraint' => '18,2',
                'null' => true,
            ],
            'operator_note' => [
                'type' => 'text',
                'null' => true,
                'default' => 'issue not define by operator/driver',
            ],
            'inserted_by' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'comment' => 'user account that input the data or use the same name from [operator_name]',
            ],
            'created_at' => [
                'type' => 'datetime',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('current_timestamp'),
                'comment' => 'this is must use the current_timestamp when record is created'
            ],
            'modified_at' => [
                'type' => 'datetime',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
                'comment' => 'it will show the last time edited'
            ],
            'deleted_at' => [
                'type' => 'datetime',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(
            ['equipment_id', 'date', 'shift', 'operator_name', 'hourmeter_start'],
            "uq_" . $this->table . '_idx'
        );

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
