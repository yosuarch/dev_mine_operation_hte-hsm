<?php

namespace App\Models\PrestartInspection;

use CodeIgniter\Model;
use CodeIgniter\Database;

class ModelPsiRecord extends Model
{
    protected $table            = 'psi_record';
    protected $primaryKey       = 'idx';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
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

    // public function getPSIRecord()
    // {
    //     return DataTable::of($this)
    //         ->add('equipment_id')
    //         ->add('date')
    //         ->add('shift')
    //         ->add('operator_name')
    //         ->add('checking_status')
    //         ->toJson();
    // }

    // fetch the record
    public function getPSIRecordDetails()
    {
        return $this->db->table($this->table)
            ->select([
                'psi_record.DATE AS date',
                // 'mp_list.`name` AS operator_name',
                // 'mp_list.employee_id AS employee_id',
                // 'general_gender.gender AS gender',
                'CONCAT(equipment_register.text_code, equipment_register.num_code) AS equipment_id',
                'equipment_models_property.model AS model',
                'equipment_models_property.type AS type',
                // 'psi_record.hourmeter_start AS hm_start',
                // 'psi_record.hourmeter_end AS hm_end',
                "GROUP_CONCAT(psi_unique_observed_item.checking_part) AS check_item",
                'ohse_danger_code.`code` AS danger_code',
                "GROUP_CONCAT(psi_record.checking_note) AS note"
            ])
            ->join('equipment_register', 'psi_record.equipment_id = equipment_register.idx', 'left')
            ->join('equipment_models_property', 'equipment_register.model = equipment_models_property.idx', 'left')
            ->join('psi_unique_observed_item', 'psi_record.checking_part = psi_unique_observed_item.idx', 'left')
            ->join('ohse_danger_code', 'psi_unique_observed_item.danger_tag = ohse_danger_code.idx', 'left')
            ->join('psi_spoting_position', 'psi_unique_observed_item.spot = psi_spoting_position.idx', 'left')
            ->join('mp_list', 'psi_record.operator_name = mp_list.idx', 'left')
            ->join('general_gender', 'mp_list.gender = general_gender.idx', 'left')
            ->join('general_working_shift', 'psi_record.shift = general_working_shift.idx', 'left')
            ->groupBy([
                'psi_record.DATE',
                'psi_record.shift',
                // 'psi_record.operator_name',
                'psi_record.equipment_id'
            ])
            ->orderBy('psi_record.DATE', 'DESC');
    }

    public function getDangerStatFreq()
    {
        // 
        return $this->db->table($this->table)
            ->select([
                'psi_record.`date` AS `date`',
                'ohse_danger_code.`code` AS `danger_code`',
                'COUNT(ohse_danger_code.`code`) AS `frequency`',
            ])
            ->join('equipment_register', 'psi_record.equipment_id = equipment_register.idx', 'left')
            ->join('psi_unique_observed_item', 'psi_record.checking_part = psi_unique_observed_item.idx', 'left')
            ->join('ohse_danger_code', 'psi_unique_observed_item.danger_tag = ohse_danger_code.idx', 'left')
            ->groupBy([
                'psi_record.`date`',
                'ohse_danger_code.`code`',
            ])
            ->orderBy('ohse_danger_code.idx', 'asc');
    }

    public function getSumIssue()
    {
        return $this->db->table('psi_record')
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
            ->groupBy('equipment_models_property.type')
            ->groupBy('CONCAT(equipment_register.class, equipment_class_uom.code)');
    }
}
