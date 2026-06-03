<?php

namespace App\Models\PrestartInspection;

use CodeIgniter\Model;
use CodeIgniter\Database;

class ModelPsiRecord extends Model
{
    protected $table                        = 'psi_record';
    protected $equipmentRegister            = 'equipment_register';
    protected $equipmentModelsProperty      = 'equipment_models_property';
    protected $primaryKey                   = 'idx';
    protected $useAutoIncrement             = true;
    protected $returnType                   = 'array';
    protected $useSoftDeletes               = true;
    protected $protectFields                = true;
    protected $allowedFields                = [
        'equipment_id',
        'date',
        'shift',
        'operator_name',
        'hourmeter_start',
        'hourmeter_end',
        'checking_part',
        'checking_status',
        'checking_note',
        'inserted_by',
    ];

    protected bool $allowEmptyInserts = true;
    protected bool $updateOnlyChanged = true;

    // protected array $casts = [];
    // protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    // protected $validationRules      = [];
    // protected $validationMessages   = [];
    // protected $skipValidation       = false;
    // protected $cleanValidationRules = true;

    // Callbacks
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = [];
    // protected $afterInsert    = [];
    // protected $beforeUpdate   = [];
    // protected $afterUpdate    = [];
    // protected $beforeFind     = [];
    // protected $afterFind      = [];
    // protected $beforeDelete   = [];
    // protected $afterDelete    = [];

    public function getDangerStatFreq()
    {
        // 
        return $this->db->table($this->table)
            ->select([
                'psi_record.`date` AS `date`',
                'ohse_danger_code.`code` AS `danger_code`',
                'COUNT(ohse_danger_code.`code`) AS `frequency`',
            ])
            ->where('psi_record.`date` >=', 'DATE_SUB(NOW(), INTERVAL 7 DAY)', false)
            ->join('equipment_register', 'psi_record.equipment_id = equipment_register.idx', 'left')
            ->join('psi_unique_observed_item', 'psi_record.checking_part = psi_unique_observed_item.idx', 'left')
            ->join('ohse_danger_code', 'psi_unique_observed_item.danger_tag = ohse_danger_code.idx', 'left')
            ->groupBy([
                'psi_record.`date`',
                'ohse_danger_code.`code`',
            ])
            ->orderBy('psi_record.`date`', 'ASC')
            ->orderBy('ohse_danger_code.idx', 'ASC');
    }

    public function getSumIssue()
    {
        return $this->db->table($this->table)
            ->select([
                "CONCAT(equipment_models_property.type, ' ', ROUND(equipment_register.class, 0), equipment_class_uom.code) AS class",
                "SUM(CASE WHEN psi_spoting_position.code = 'inside' THEN 1 ELSE NULL END) AS inside",
                "SUM(CASE WHEN psi_spoting_position.code = 'outside' THEN 1 ELSE NULL END) AS outside",
                "SUM(CASE WHEN psi_spoting_position.code = 'safety_device' THEN 1 ELSE NULL END) AS safety_device"
            ])
            ->join('psi_unique_observed_item', 'psi_record.checking_part = psi_unique_observed_item.idx', 'left')
            ->join('equipment_register', 'psi_record.equipment_id = equipment_register.idx', 'left')
            ->join('equipment_models_property', 'equipment_register.model = equipment_models_property.idx', 'left')
            ->join('equipment_class_uom', 'equipment_register.class_uom = equipment_class_uom.idx', 'left')
            ->join('psi_spoting_position', 'psi_unique_observed_item.spot = psi_spoting_position.idx', 'left')
            ->where('psi_record.`date` = (SELECT MAX(date) FROM psi_record)', null, false)
            ->groupBy('equipment_models_property.type')
            ->groupBy('CONCAT(equipment_register.class, equipment_class_uom.code)');
    }

    public function getDailyUniqueEquipmentType()
    {
        // 1. Get the latest date subquery
        $subQuery = $this->db->table('psi_record')
            ->selectMax('date');

        // 2. Build the main query
        return $this->builder() // Assumes your model has a builder initialized
            ->select('equipment_models_property.type')
            ->distinct()
            ->join('equipment_register', 'psi_record.equipment_id = equipment_register.idx', 'left')
            ->join('equipment_models_property', 'equipment_register.model = equipment_models_property.idx', 'left')
            ->where('psi_record.date = (' . $subQuery->getCompiledSelect() . ')');
    }
}
