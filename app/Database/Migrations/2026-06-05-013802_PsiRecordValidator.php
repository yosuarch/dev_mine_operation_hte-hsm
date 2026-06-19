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
                'null' => false,
                'comment' => 'this value will be populated from trigger event',
            ],
            'equipment_id' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
                'comment' => 'this value will be populated from trigger event',
            ],
            'shift' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
                'comment' => 'this value will be populated from trigger event',
            ],
            'operator_name' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
                'comment' => 'this value will be populated from trigger event',
            ],
            'hm_start' => [
                'type' => 'decimal',
                'constraint' => '14,2',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
                'comment' => 'this value will be populated from trigger event',
            ],
            'hm_end' => [
                'type' => 'decimal',
                'constraint' => '14,2',
                'null' => true,
                'default' => new \CodeIgniter\Database\RawSql('null'),
                'comment' => 'this value will be populated from trigger event',
            ],
            'checked_part' => [
                'type' => 'int',
                'constraint' => 4,
                'unsigned' => true,
                'null' => false,
                'comment' => 'this value will be populated from trigger event',
            ],
            'operator_note' => [
                'type' => 'text',
                'null' => false,
                'comment' => 'this value will be populated from trigger event',
            ],
            'isvalid_fm' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'null' => true,
                'default' => 0,
                'comment' => 'the validator with position as a foreman',
            ],
            'fm_name' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => true,
            ],
            'fm_note' => [
                'type' => 'text',
                'null' => true,
                'comment' => 'the note from foreman for row value',
            ],
            'isvalid_spv' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'null' => true,
                'default' => 0,
                'comment' => 'the validator with position as a foreman',
            ],
            'spv_name' => [
                'type' => 'int',
                'constraint' => 3,
                'null' => true,
            ],
            'spv_note' => [
                'type' => 'text',
                'null' => true,
                'comment' => 'the note from supervisor for row value',
            ],
        ]);

        // table attribute
        $this->forge->addPrimaryKey('idx');
        $this->forge->addUniqueKey(
            ['date', 'shift', 'equipment_id', 'operator_name', 'hm_start', 'hm_end', 'checked_part'],
            "uq_" . $this->table . '_idx'
        );
        $this->forge->addUniqueKey(
            ['date', 'shift', 'equipment_id', 'operator_name', 'hm_start', 'hm_end', 'checked_part'],
            'idx_psi_validator_lookup'
        );

        // create the table
        $this->forge->createTable($this->table, true, ['comment' => 'for indexing on this table we dont use the primary key field inside this table']);

        $db = Database::connect();

        // create insert trigger
        $db->query("
        CREATE TRIGGER after_psi_record_insert
        AFTER INSERT ON psi_record
        FOR EACH ROW
        BEGIN
            DECLARE v_isvalid_fm INT DEFAULT 0;
            DECLARE v_isvalid_spv INT DEFAULT 0;

            IF NEW.fm_name <> 0 THEN
                SET v_isvalid_fm = 1;
            END IF;

            IF NEW.spv_name <> 0 THEN
                SET v_isvalid_spv = 1;
            END IF;

            INSERT INTO {$this->table}
            (
                `date`, `equipment_id`, `shift`, `operator_name`, `hm_start`, `hm_end`,
                `checked_part`, `operator_note`, `fm_name`, `fm_note`, `spv_name`, `spv_note`,
                `isvalid_fm`, `isvalid_spv`
            )
            VALUES
            (
                NEW.`date`, NEW.equipment_id, NEW.`shift`, NEW.operator_name, NEW.hourmeter_start,
                NEW.hourmeter_end, NEW.checking_part, NEW.checking_note, NEW.fm_name, NEW.fm_note,
                NEW.spv_name, NEW.spv_note, v_isvalid_fm, v_isvalid_spv
            )
            ON DUPLICATE KEY UPDATE
                operator_note = VALUES(operator_note),
                fm_name       = VALUES(fm_name),
                fm_note       = VALUES(fm_note),
                spv_name      = VALUES(spv_name),
                spv_note      = VALUES(spv_note),
                isvalid_fm    = VALUES(isvalid_fm),
                isvalid_spv   = VALUES(isvalid_spv);
        END
        ");

        // create update trigger
        $db->query("
        CREATE TRIGGER after_psi_record_update
        AFTER UPDATE ON psi_record
        FOR EACH ROW
        BEGIN
            UPDATE {$this->table}
            SET
                `date`         = NEW.`date`,
                `equipment_id` = NEW.equipment_id,
                `shift`        = NEW.`shift`,
                `operator_name`= NEW.operator_name,
                `hm_start`     = NEW.hourmeter_start,
                `hm_end`       = NEW.hourmeter_end,
                `checked_part` = NEW.checking_part,
                `operator_note`= NEW.checking_note,
                `fm_name`      = NEW.fm_name,
                `fm_note`      = NEW.fm_note,
                `spv_name`     = NEW.spv_name,
                `spv_note`     = NEW.spv_note
            WHERE `date`          = OLD.`date`
              AND `equipment_id`  = OLD.equipment_id
              AND `shift`         = OLD.`shift`
              AND `operator_name` = OLD.operator_name
              AND `hm_start`      = OLD.hourmeter_start
              AND `hm_end`        = OLD.hourmeter_end
              AND `checked_part`  = OLD.checking_part
              AND `fm_name`       = OLD.fm_name
              AND `fm_note`       = OLD.fm_note
              AND `spv_name`      = OLD.spv_name
              AND `spv_note`      = OLD.spv_note;
        END
        ");

        // create delete trigger
        $db->query("
        CREATE TRIGGER after_psi_record_delete
        AFTER DELETE ON psi_record
        FOR EACH ROW
        BEGIN
            DELETE FROM {$this->table}
            WHERE `date`          = OLD.`date`
              AND `equipment_id`  = OLD.equipment_id
              AND `shift`         = OLD.`shift`
              AND `operator_name` = OLD.operator_name
              AND `hm_start`      = OLD.hourmeter_start
              AND `hm_end`        = OLD.hourmeter_end
              AND `checked_part`  = OLD.checking_part
              AND `fm_name`       = OLD.fm_name
              AND `fm_note`       = OLD.fm_note
              AND `spv_name`      = OLD.spv_name
              AND `spv_note`      = OLD.spv_note;
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
