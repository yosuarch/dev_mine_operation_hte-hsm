<?php

namespace App\Controllers\Manpower;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ControllerManpower extends BaseController
{
    public function index()
    {
        // render the view
        $data = [
            'pageTitle' => 'Manpower Database | Manpower',
            'pageSubTitle' => 'none'
        ];
        return view('/pages/manpower/idx-manpower', $data);
    }

    /**
     * Shared validation for store()/update() — returns an error message
     * string, or null if the payload is valid.
     *
     * Role is required for new inserts (mp_list.role -> mp_mine_operation_dept_role.idx,
     * see Trilium note 13) but optional on edit — 257 existing employees predate
     * this field and still carry the migration default (999); editing a phone
     * number shouldn't force a role choice on them. sub_departement remains
     * untouched (out of scope for this form).
     */
    private function validateManpower(array $json, ?int $excludeIdx = null): ?string
    {
        $db = \Config\Database::connect();

        $isEdit     = $excludeIdx !== null;
        $name       = trim($json['name'] ?? '');
        $employeeId = trim((string) ($json['employee_id'] ?? ''));
        $genderId   = (int) ($json['gender'] ?? 0);
        $roleId     = ($json['role'] ?? '') !== '' ? (int) $json['role'] : null;

        if (!$name || !$employeeId || !$genderId) {
            return 'Name, Employee ID, and Gender are required.';
        }

        if (!$isEdit && !$roleId) {
            return 'Role is required.';
        }

        if (!ctype_digit($employeeId)) {
            return 'Employee ID must be numeric.';
        }

        $genderExists = $db->table('general_gender')->where('idx', $genderId)->countAllResults() > 0;
        if (!$genderExists) {
            return 'Invalid gender selection.';
        }

        if ($roleId !== null) {
            $roleExists = $db->table('mp_mine_operation_dept_role')->where('idx', $roleId)->countAllResults() > 0;
            if (!$roleExists) {
                return 'Invalid role selection.';
            }
        }

        $dupeCheck = $db->table('mp_list')->where('employee_id', $employeeId);
        if ($excludeIdx !== null) {
            $dupeCheck->where('idx !=', $excludeIdx);
        }
        if ($dupeCheck->countAllResults() > 0) {
            return "Employee ID {$employeeId} is already registered.";
        }

        return null;
    }

    public function store()
    {
        $json = $this->request->getJSON(true);

        $error = $this->validateManpower($json);
        if ($error !== null) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => $error,
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $db = \Config\Database::connect();

        try {
            $db->table('mp_list')->insert([
                'name'                      => trim($json['name']),
                'employee_id'               => (int) $json['employee_id'],
                'gender'                    => (int) $json['gender'],
                'role'                      => (int) $json['role'],
                'first_phone_number'        => trim($json['first_phone_number'] ?? '') ?: null,
                'second_phone_number'       => trim($json['second_phone_number'] ?? '') ?: null,
                'emergency_contact_number'  => trim($json['emergency_contact_number'] ?? '') ?: null,
            ]);
        } catch (\Throwable) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'    => 'error',
                'message'   => 'This name/Employee ID/Gender combination already exists.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status'    => 'ok',
            'idx'       => $db->insertID(),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function update(int $idx)
    {
        $json = $this->request->getJSON(true);

        $error = $this->validateManpower($json, $idx);
        if ($error !== null) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => $error,
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $db = \Config\Database::connect();

        $update = [
            'name'                      => trim($json['name']),
            'employee_id'               => (int) $json['employee_id'],
            'gender'                    => (int) $json['gender'],
            'first_phone_number'        => trim($json['first_phone_number'] ?? '') ?: null,
            'second_phone_number'       => trim($json['second_phone_number'] ?? '') ?: null,
            'emergency_contact_number'  => trim($json['emergency_contact_number'] ?? '') ?: null,
        ];
        // Only touch role if the form actually sent one — don't force existing
        // (pre-role-field) employees to lose/reset it on an unrelated edit.
        if (($json['role'] ?? '') !== '') {
            $update['role'] = (int) $json['role'];
        }

        try {
            $db->table('mp_list')->where('idx', $idx)->update($update);
        } catch (\Throwable) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'    => 'error',
                'message'   => 'This name/Employee ID/Gender combination already exists.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'status'    => 'ok',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function destroy(int $idx)
    {
        $db = \Config\Database::connect();

        // Guard: block deletion if this employee has historical P2H, hour-meter,
        // or hauling-activity records — hard-deleting would orphan those FKs.
        $refCount = $db->table('psi_record')->where('operator_name', $idx)->countAllResults()
            + $db->table('wh_recording')->where('operator_name', $idx)->countAllResults()
            + $db->table('dumptruck_activity_record')->where('hauler_drvr_id', $idx)->countAllResults()
            + $db->table('dumptruck_activity_record')->where('loader_opr_id', $idx)->countAllResults();

        if ($refCount > 0) {
            return $this->response->setStatusCode(409)->setJSON([
                'status'    => 'error',
                'message'   => "Cannot delete — referenced by {$refCount} existing record(s) (P2H, hour-meter, or hauling activity).",
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $db->table('mp_list')->where('idx', $idx)->delete();

        return $this->response->setJSON([
            'status'    => 'ok',
            'csrf_hash' => csrf_hash(),
        ]);
    }
}
