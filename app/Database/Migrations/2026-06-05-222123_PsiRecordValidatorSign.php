<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiRecordValidatorSign extends Migration
{

    // common info
    protected $table = 'psi_record_validator_sign';
    protected $DBGroup = 'default';

    public function up()
    {
        // create the table
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'validator_name' => [
                'type' => 'int',
                'constraint' => '3',
                'null' => false,
            ],
            'validator_sub_dept_role' => [
                'type' => 'int',
                'constraint' => '3',
                'null' => false,
            ],
            'validator_signature' => [
                'type' => 'mediumblob',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'timestamp',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('current_timestamp'),
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['validator_name', 'validator_sub_dept_role'], "uq_" . $this->table . '_idx');
        // create the table
        $this->forge->createTable($this->table, true, ['comment' => 'this table is only to make a record as json of the upload status']);
    }

    public function down()
    {
        //
        $this->forge->dropTable($this->table, true);
    }
}
