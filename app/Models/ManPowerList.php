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
                'mp_list.gender AS gender_id',
                'general_gender.gender AS gender_label',
                'mp_list.role AS role_id',
                "REPLACE(mp_mine_operation_dept_role.job_title, '_', ' ') AS job_title",
                'mp_list.first_phone_number AS phone_number',
                'mp_list.second_phone_number AS second_phone_number',
                'mp_list.emergency_contact_number AS emergency_contact_number',
            ])
            ->join('general_gender', 'mp_list.gender = general_gender.idx', 'left')
            ->join('mp_mine_operation_dept_role', 'mp_list.role = mp_mine_operation_dept_role.idx', 'left');
    }

    /** Options for the gender <select> in the Add/Edit form. */
    public function fetchGenderOptions(): array
    {
        return $this->db->table('general_gender')
            ->select('idx, gender')
            ->orderBy('idx', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Options for the Role <select> in the Add/Edit form — mp_list.role
     * points at mp_mine_operation_dept_role.idx (job title), which in turn
     * points at mp_role.idx via role_class (role/classification/abbreviation).
     * See Trilium note 13 for how this two-level mapping was confirmed.
     */
    public function fetchRoleOptions(): array
    {
        return $this->db->table('mp_mine_operation_dept_role dr')
            ->select([
                'dr.idx AS idx',
                "REPLACE(dr.job_title, '_', ' ') AS job_title",
                'mr.abbreviation AS abbreviation',
            ])
            ->join('mp_role mr', 'dr.role_class = mr.idx', 'left')
            ->orderBy('dr.job_title', 'ASC')
            ->get()->getResultArray();
    }

    /** Headcount by gender, for the KPI cards. */
    public function fetchGenderKpi(): array
    {
        $rows = $this->db->table($this->table)
            ->select('general_gender.gender AS gender, COUNT(*) AS total')
            ->join('general_gender', 'mp_list.gender = general_gender.idx', 'left')
            ->groupBy('general_gender.gender')
            ->get()->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['gender'] ?? 'unknown'] = (int) $r['total'];
        }

        return [
            'total' => array_sum($map),
            'man'   => $map['man']   ?? 0,
            'woman' => $map['woman'] ?? 0,
        ];
    }
}
