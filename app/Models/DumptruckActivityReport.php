<?php

namespace App\Models;

use CodeIgniter\Model;

class DumptruckActivityReport extends Model
{
    protected $table      = 'view_dumptruck_activity';
    protected $primaryKey = 'idx';
    protected $returnType = 'array';

    /**
     * Query builder for the DataTables ajax endpoint — view_dumptruck_activity
     * already excludes undone (soft-deleted) trips internally.
     *
     * @param array{date_from?: string, date_to?: string, shift?: string, hauler_id?: string, mat_category?: string} $filters
     */
    public function fetchHaulingActivityList(array $filters = [])
    {
        // Hermawan\DataTables introspects the compiled SELECT to derive columns,
        // so the field list must be explicit — select('*') leaves it empty.
        $builder = $this->db->table($this->table)->select([
            'idx',
            'date',
            'time',
            'shift',
            'loader_id',
            'mp_loader',
            'hauler_id',
            'mp_hauler',
            'mat_source',
            'mat_source_note',
            'mat_destination',
            'mat_dest_note',
            'mat_category',
            'sub_material',
            'material_note',
            'driver_note',
        ]);

        if (!empty($filters['date_from'])) {
            $builder->where('date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('date <=', $filters['date_to']);
        }
        if (!empty($filters['shift'])) {
            $builder->where('shift', $filters['shift']);
        }
        if (!empty($filters['hauler_id'])) {
            $builder->where('hauler_id', $filters['hauler_id']);
        }
        if (!empty($filters['mat_category'])) {
            $builder->where('mat_category', $filters['mat_category']);
        }

        return $builder->orderBy('date', 'DESC')->orderBy('time', 'DESC');
    }

    /**
     * view_equipment_class.formatted_class_info values actually used by
     * haulers that show up in view_dumptruck_activity (verified live —
     * dumptruck-type equipment only, per DUMPTRUCK_TYPE_IDX in
     * ControllerOperatorDriver: ADT + DUMP TRUCK).
     */
    private const HAULER_CLASSES = [
        'adt_40ton'        => 'ADT',
        'dump_truck_20ton' => 'Dumptruck 20 Ton',
        'dump_truck_40ton' => 'Dumptruck 40 Ton',
    ];

    public function fetchKpiToday(): array
    {
        $today = date('Y-m-d');

        $tripsByClass = $this->db->table($this->table . ' dar')
            ->select('vec.formatted_class_info AS class_info, COUNT(*) AS trip_count')
            ->join('view_equipment_class vec', 'dar.hauler_id = vec.code_concat', 'left')
            ->where('dar.date', $today)
            ->groupBy('vec.formatted_class_info')
            ->get()->getResultArray();

        $tripsByClassMap = array_fill_keys(array_keys(self::HAULER_CLASSES), 0);
        foreach ($tripsByClass as $row) {
            if (isset($tripsByClassMap[$row['class_info']])) {
                $tripsByClassMap[$row['class_info']] = (int) $row['trip_count'];
            }
        }

        $haulers = $this->db->table($this->table)
            ->select('hauler_id')
            ->where('date', $today)
            ->distinct()
            ->countAllResults();

        $topMaterial = $this->db->table($this->table)
            ->select('mat_category, COUNT(*) AS trip_count')
            ->where('date', $today)
            ->groupBy('mat_category')
            ->orderBy('trip_count', 'DESC')
            ->get(1)
            ->getRowArray();

        return [
            'tripsToday'       => array_sum($tripsByClassMap),
            'tripsByClass'     => $tripsByClassMap,
            'haulersToday'     => $haulers,
            'topMaterial'      => $topMaterial['mat_category'] ?? null,
            'topMaterialCount' => (int) ($topMaterial['trip_count'] ?? 0),
        ];
    }

    /**
     * Cheap "has anything changed" heartbeat for polling — a plain row count
     * moves on both new trips (insert) and undos (soft-delete), unlike
     * MAX(idx) which wouldn't reflect a deleted non-max row.
     */
    public function getActivityCount(): int
    {
        return $this->db->table($this->table)->countAllResults();
    }

    /** Distinct values for the filter dropdowns, drawn from the view itself. */
    public function fetchFilterOptions(): array
    {
        return [
            'haulers' => $this->db->table($this->table)
                ->select('hauler_id, mp_hauler')
                ->groupBy('hauler_id, mp_hauler')
                ->orderBy('hauler_id', 'ASC')
                ->get()->getResultArray(),
            'materials' => $this->db->table($this->table)
                ->select('mat_category')
                ->groupBy('mat_category')
                ->orderBy('mat_category', 'ASC')
                ->get()->getResultArray(),
        ];
    }
}
