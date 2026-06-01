<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiRecord extends Migration
{

    // common info
    protected $table = 'psi_record';
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
                'null' => false,
            ],
            'date' => [
                'type' => 'date',
            ],
            'shift' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'null' => false,
                'default' => 0
            ],
            'operator_name' => [
                'type' => 'int',
                'constraint' => 5,
                'unsigned' => true,
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
                'comment' => 'this field must have content'
            ],
            'checking_status' => [
                'type' => 'tinyint',
                'constraint' => 1,
            ],
            'checking_note' => [
                'type' => 'text',
                'default' => 'no issue',
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
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['equipment_id', 'date', 'shift', 'operator_name', 'hourmeter_start', 'hourmeter_end', 'checking_part'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        // drop the table
        $this->forge->dropTable($this->table);
    }
}
