<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NotificationTarget extends Migration
{
    protected $table   = 'notification_target';
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
                'comment'    => 'display label, used as email To: name',
            ],
            'channel' => [
                'type'       => 'enum',
                'constraint' => ['email', 'whatsapp'],
                'null'       => false,
                'default'    => 'email',
            ],
            'contact' => [
                'type'       => 'varchar',
                'constraint' => 150,
                'null'       => false,
                'comment'    => 'email address or phone number (intl format without +)',
            ],
            'notify_on' => [
                'type'       => 'enum',
                'constraint' => ['always', 'abnormal_only'],
                'null'       => false,
                'default'    => 'always',
                'comment'    => 'always = every P2H submit; abnormal_only = only when not-normal items exist',
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
