<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class PsiRecordValidator extends Migration
{

    // common info
    protected $table = 'psi_record_validator';
    protected $DBGroup = 'default';

    public function up()
    {
        // create the field
        $this->forge->addField([
            'idx' => [
                'type' => 'int',
                'constraint' => 6,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'date' => [
                'type' => 'date',
                'comment' => 'this value will be populated from trigger event'
            ],
            'equipment_id' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'comment' => 'this value will be populated from trigger event'
            ],
            'shift' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'comment' => 'this value will be populated from trigger event'
            ],
            'operator_name' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'comment' => 'this value will be populated from trigger event'
            ],
            'hm_start' => [
                'type' => 'decimal',
                'constraint' => '14,2',
                'comment' => 'this value will be populated from trigger event'
            ],
            'hm_end' => [
                'type' => 'decimal',
                'constraint' => '14,2',
                'comment' => 'this value will be populated from trigger event'
            ],
            'checked_part' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'comment' => 'this value will be populated from trigger event'
            ],
            'operator_note' => [
                'type' => 'text',
                'comment' => 'this value will be populated from trigger event'
            ],
            'isvalid_fm' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'null' => false,
                'default' => 0,
                'comment' => 'the validator with position as a foreman'
            ],
            'fm_name' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => true,
            ],
            'fm_note' => [
                'type' => 'text',
                'comment' => 'the note from foreman for row value',
                'null' => true
            ],
            'isvalid_spv' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 0,
                'null' => false,
                'comment' => 'the validator with position as a foreman'
            ],
            'spv_name' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => true,
            ],
            'spv_note' => [
                'type' => 'text',
                'comment' => 'the note from supervisor for row value',
                'null' => true
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(['date', 'shift', 'equipment_id', 'operator_name', 'hm_start', 'hm_end', 'checked_part'], "uq_" . $this->table . '_idx');

        // create the table
        $this->forge->createTable($this->table, true, ['comment' => 'for indexing on this table we dont use the primary key field inside this table']);

        $db = Database::connect();

        // create insert trigger
        $db->query('
        create trigger after_psi_record_insert
        after insert on dev_hte.psi_record
        for each row
        begin
        insert into dev_hte.' . $this->table . '
        (`date`, `equipment_id`, `shift`, `operator_name`, `hm_start`, `hm_end`, `checked_part`, `operator_note`)
        VALUES (NEW.`date`, NEW.equipment_id, NEW.`shift`, NEW.operator_name, NEW.hourmeter_start, NEW.hourmeter_end, NEW.checking_part, NEW.checking_note)
        on duplicate key update
        operator_note = values(operator_note);
        end
        ');

        // create update trigger
        $db->query("
        CREATE TRIGGER after_psi_record_update
        AFTER UPDATE ON dev_hte.psi_record
        FOR EACH ROW
        BEGIN
        UPDATE dev_hte.{$this->table}
        SET 
            `date` = NEW.`date`,
            `equipment_id` = NEW.equipment_id,
            `shift` = NEW.`shift`,
            `operator_name` = NEW.operator_name,
            `hm_start` = NEW.hourmeter_start,
            `hm_end` = NEW.hourmeter_end,
            `checked_part` = NEW.checking_part,
            `operator_note` = NEW.checking_note
        WHERE `date` = OLD.`date` 
          AND `equipment_id` = OLD.equipment_id 
          AND `shift` = OLD.`shift` 
          AND `operator_name` = OLD.operator_name 
          AND `hm_start` = OLD.hourmeter_start 
          AND `hm_end` = OLD.hourmeter_end
          AND `checked_part` = OLD.checking_part;
        END
        ");

        // create delete trigger
        $db->query("
            CREATE TRIGGER after_psi_record_delete
            AFTER DELETE ON dev_hte.psi_record
            FOR EACH ROW
            BEGIN
                DELETE FROM dev_hte.{$this->table}
                WHERE `date` = OLD.`date` 
                AND `equipment_id` = OLD.equipment_id 
                AND `shift` = OLD.`shift` 
                AND `operator_name` = OLD.operator_name 
                AND `hm_start` = OLD.hourmeter_start 
                AND `hm_end` = OLD.hourmeter_end
                AND `checked_part` = OLD.checking_part;
            END
        ");
    }

    public function down()
    {
        $db = Database::connect();
        $db->query("DROP TRIGGER IF EXISTS after_psi_record_insert");
        $db->query("DROP TRIGGER IF EXISTS after_psi_record_update");
        $db->query("DROP TRIGGER IF EXISTS after_psi_record_delete");
        // drop the table
        $this->forge->dropTable($this->table, true);
    }
}
