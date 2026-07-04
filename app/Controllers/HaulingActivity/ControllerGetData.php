<?php

namespace App\Controllers\HaulingActivity;

use App\Controllers\BaseController;
use App\Models\DumptruckActivityReport;
use Hermawan\DataTables\DataTable;

class ControllerGetData extends BaseController
{
    public function fetchHaulingActivityList()
    {
        $model = new DumptruckActivityReport();

        $query = $model->fetchHaulingActivityList([
            'date_from'    => $this->request->getGet('date_from'),
            'date_to'      => $this->request->getGet('date_to'),
            'shift'        => $this->request->getGet('shift'),
            'hauler_id'    => $this->request->getGet('hauler_id'),
            'mat_category' => $this->request->getGet('mat_category'),
        ]);

        // returnAsObject=true so the view can read named fields (row.date, row.mat_category, ...)
        return DataTable::of($query)->toJson(true);
    }

    public function kpiData()
    {
        $model = new DumptruckActivityReport();

        return $this->response->setJSON([
            'kpi'     => $model->fetchKpiToday(),
            'filters' => $model->fetchFilterOptions(),
            'count'   => $model->getActivityCount(),
        ]);
    }

    /** Cheap poll target — the page calls this often and only does a full refresh when `count` changes. */
    public function activityCount()
    {
        $model = new DumptruckActivityReport();

        return $this->response->setJSON([
            'count' => $model->getActivityCount(),
        ]);
    }
}
