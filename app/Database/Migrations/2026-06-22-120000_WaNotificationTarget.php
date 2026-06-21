<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WaNotificationTarget extends Migration
{
    protected $table   = 'wa_notification_target';
    protected $DBGroup = 'default';

    public function up()
    {
        $this->forge->addField([
            'idx' => [
                'type'           => 'int',
                'constraint'     => 3,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'varchar',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'display label for this recipient',
            ],
            'phone_number' => [
                'type'       => 'varchar',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'international format without +, e.g. 628123456789',
            ],
            'active' => [
                'type'       => 'tinyint',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
            ],
            'created_at' => [
                'type'    => 'datetime',
                'null'    => false,
                'default' => new \CodeIgniter\Database\RawSql('current_timestamp'),
            ],
            'deleted_at' => [
                'type'    => 'datetime',
                'null'    => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
            ],
        ]);

        $this->forge->addPrimaryKey('idx');
        $this->forge->createTable($this->table);
    }

    public function down()
    {
        $this->forge->dropTable($this->table, true);
    }
}
