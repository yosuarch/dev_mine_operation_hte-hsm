<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardRender extends BaseController
{
    public function index()
    {
        return view('pages/idx-dashboard', array_merge(
            ['pageTitle' => 'Dashboard'],
            $this->buildData()
        ));
    }

    public function ajaxData()
    {
        return $this->response->setJSON($this->buildData());
    }

    private function buildData(): array
    {
        $db  = \Config\Database::connect();
        $tz  = new \DateTimeZone('Asia/Jayapura');
        $now = new \DateTime('now', $tz);

        $today = $now->format('Y-m-d');

        // Last 7 dates that have actual wh_recording data, always include today
        $dbDates = array_column(
            $db->query(
                "SELECT DISTINCT date FROM wh_recording
                 WHERE deleted_at IS NULL ORDER BY date DESC LIMIT 7"
            )->getResultArray(),
            'date'
        );

        if (!in_array($today, $dbDates)) {
            array_unshift($dbDates, $today);
            $dbDates = array_slice($dbDates, 0, 7);
        }

        sort($dbDates);
        $weekDates = $dbDates;
        $weekStart = $weekDates[0];

        // KPI: today's total P2H submissions
        $todayWh = (int) $db->query(
            "SELECT COUNT(*) AS cnt FROM wh_recording WHERE date = ? AND deleted_at IS NULL",
            [$today]
        )->getRow()->cnt;

        // KPI: today's P2H with abnormal items
        $todayPsi = (int) $db->query(
            "SELECT COUNT(DISTINCT equipment_id, shift, operator_name, hourmeter_start) AS cnt
             FROM psi_record WHERE date = ? AND deleted_at IS NULL",
            [$today]
        )->getRow()->cnt;

        // KPI: active equipment today
        $todayEquip = (int) $db->query(
            "SELECT COUNT(DISTINCT equipment_id) AS cnt FROM wh_recording WHERE date = ? AND deleted_at IS NULL",
            [$today]
        )->getRow()->cnt;

        // Trend per active date
        $whTrend = array_fill_keys($weekDates, 0);
        foreach ($db->query(
            "SELECT date, COUNT(*) AS cnt FROM wh_recording
             WHERE date BETWEEN ? AND ? AND deleted_at IS NULL GROUP BY date",
            [$weekStart, $today]
        )->getResultArray() as $r) {
            $whTrend[$r['date']] = (int) $r['cnt'];
        }

        $psiTrend = array_fill_keys($weekDates, 0);
        foreach ($db->query(
            "SELECT date, COUNT(DISTINCT equipment_id, shift, operator_name, hourmeter_start) AS cnt
             FROM psi_record WHERE date BETWEEN ? AND ? AND deleted_at IS NULL GROUP BY date",
            [$weekStart, $today]
        )->getResultArray() as $r) {
            $psiTrend[$r['date']] = (int) $r['cnt'];
        }

        // Equipment with activity in the active date range only
        $equipList = $db->query(
            "SELECT er.idx, CONCAT(er.text_code, er.num_code) AS label
             FROM equipment_register er
             INNER JOIN wh_recording wr ON wr.equipment_id = er.idx
                 AND wr.date BETWEEN ? AND ? AND wr.deleted_at IS NULL
             WHERE er.deleted_at IS NULL
             GROUP BY er.idx, er.text_code, er.num_code
             ORDER BY er.text_code, er.num_code",
            [$weekStart, $today]
        )->getResultArray();

        // Equipment × date grid
        $whGrid = [];
        foreach ($db->query(
            "SELECT equipment_id, date, COUNT(*) AS cnt FROM wh_recording
             WHERE date BETWEEN ? AND ? AND deleted_at IS NULL GROUP BY equipment_id, date",
            [$weekStart, $today]
        )->getResultArray() as $r) {
            $whGrid[$r['equipment_id']][$r['date']] = (int) $r['cnt'];
        }

        return [
            'today'      => $today,
            'todayLabel' => date('d F Y', strtotime($today)),
            'weekDates'  => $weekDates,
            'dateLabels' => array_map(fn($d) => date('d M', strtotime($d)), $weekDates),
            'todayWh'    => $todayWh,
            'todayPsi'   => $todayPsi,
            'todayEquip' => $todayEquip,
            'whTrend'    => array_values($whTrend),
            'psiTrend'   => array_values($psiTrend),
            'equipList'  => $equipList,
            'whGrid'     => $whGrid,
        ];
    }
}
