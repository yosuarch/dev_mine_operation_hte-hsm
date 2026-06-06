<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PsiUploadLogs extends Migration
{

    // common info
    protected $table = 'psi_record_upload_results';
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
            'summary_json' => [
                'type' => 'longtext',
                'default' => 'NULL',
            ],
            'created_at' => [
                'type' => 'timestamp',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('current_timestamp'),
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        // create the table
        $this->forge->createTable($this->table, true, ['comment' => 'this table is only to make a record as json of the upload status']);
    }

    public function down()
    {
        //
        $this->forge->dropTable($this->table, true);
    }
}
