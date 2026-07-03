<?php

namespace App\Models;

use CodeIgniter\Model;

class ManPowerList extends Model
{
    protected $table            = 'mp_list';
    protected $primaryKey       = 'idx';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employee_id',
        'gender',
        'first_phone_number',
        'second_phone_number',
        'emergency_contact_number',
    ];

    protected bool $allowEmptyInserts = true;
    // protected bool $updateOnlyChanged = true;

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

    public function fetchManpPowerList()
    {
        //
        return $this->db->table($this->table)
            ->select([
                'mp_list.idx AS idx',
                'mp_list.name AS name',
                'mp_list.employee_id AS employee_id',
                'general_gender.gender AS gender',
                'mp_list.first_phone_number AS phone_number',
            ])
            ->join('general_gender', 'mp_list.gender = general_gender.idx', 'left');
    }
}
