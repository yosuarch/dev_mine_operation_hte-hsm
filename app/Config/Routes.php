<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// 1. PUBLIC ROUTES (Landing Page)
// This fixes the 404 error on GET: /
$routes->get('/', 'ControllerLandingPage::index');
$routes->get('/landing', 'ControllerLandingPage::index');

// 2. PROTECTED ROUTES (Requires Login)
// Use Shield's native 'session' filter instead of a custom one
$routes->group('', ['filter' => 'session'], function ($routes) {

    // Pages
    $routes->get('/admin', 'DashboardRender::index');
    $routes->get('/prestart-insepction', 'PrestartInspection\ControllerPSI::index');
    $routes->get('/manpower', 'Manpower\ControllerManpower::index');

    // Uploads & Ajax (Protected)
    $routes->post('/preview-psi-record', 'PrestartInspection\PreviewBeforeUpload::index');
    $routes->post('/upload-psi-record', 'PrestartInspection\UploadPSIRecord::index');
    $routes->get('/ajax-datatable/prestartrecord', 'PrestartInspection\ControllerGetData::fetchPSIDetail');
    $routes->get('/ajax-common-table/psi-by-equipment-type', 'PrestartInspection\ControllerGetData::getSumIssue');
    $routes->get('/ajax-datatable/manpowerlist', 'Manpower\ControllerGetData::fetchManPowerList');
    $routes->get('/ajax-chart/freq-danger-code', 'PrestartInspection\ControllerGetData::fetchGetDangerCodeFreq');
    $routes->get('/ajax-daily-unit-type', 'PrestartInspection\ControllerEmailReport::getData1');

    // Testing
    $routes->get('/test-mail', 'TestMail::sendTestEmail');
    $routes->get('/psi-generate-pdf', 'PrestartInspection\ControllerSentReportTest::psiDailyDetailReport');
    $routes->get('/psi-generate-excel', 'PrestartInspection\ControllerGetExcelReport::index');
});

service('auth')->routes($routes);
