<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiRecord extends Migration
{

    // common info
    protected $table = 'psi_record';
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
            'checking_part' => [
                'type' => 'int',
                'constraint' => 6,
                'unsigned' => true,
                'null' => false,
                'comment' => 'this field must have content',
            ],
            'checking_status' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'null' => false,
            ],
            'checking_note' => [
                'type' => 'text',
                'null' => true,
                'default' => 'issue not define by operator/driver',
            ],
            'inserted_by' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'comment' => 'user account that input the data',
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
            'fm_name' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'comment' => 'user account that input the data',
            ],
            'fm_note' => [
                'type' => 'text',
                'null' => true,
                'default' => 'this is data is a dummy_please check the actual sheet to make confirmation or ask the admin',
            ],
            'spv_name' => [
                'type' => 'int',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'comment' => 'user account that input the data',
            ],
            'spv_note' => [
                'type' => 'text',
                'null' => true,
                'default' => 'this is data is a dummy_please check the actual sheet to make confirmation or ask the admin',
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(
            ['equipment_id', 'date', 'shift', 'operator_name', 'hourmeter_start', 'checking_part'],
            "uq_" . $this->table . '_idx'
        );
        $this->forge->addUniqueKey(
            ['date', 'shift', 'equipment_id', 'operator_name', 'hourmeter_start', 'checking_part'],
            'idx_psi_lookup'
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
