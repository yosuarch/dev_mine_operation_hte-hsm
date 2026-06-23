<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// 1. PUBLIC ROUTES (Landing Page)
// This fixes the 404 error on GET: /
$routes->get('/', 'ControllerLandingPage::index');
$routes->get('/landing', 'ControllerLandingPage::index');
$routes->get('/operator-driver', 'PrestartInspection\Mobile\ControllerOperatorDriver::index');
$routes->get('/operator-driver/operator-driver-name-id', 'PrestartInspection\ControllerGetData::fetchMPList');
$routes->get('/operator-driver/unique-equipment-type', 'PrestartInspection\ControllerGetData::getUniqueEquipmentType');
$routes->get('/operator-driver/equipment-id', 'PrestartInspection\ControllerGetData::getEquipmentID');
$routes->get('/operator-driver/psi-fetch-form', 'PrestartInspection\ControllerGetData::getEmptyPSIForm');
$routes->post('/operator-driver/submit-psi', 'PrestartInspection\Mobile\ControllerOperatorDriver::submitPSI');
$routes->get('/operator-driver/check-open-shift', 'PrestartInspection\Mobile\ControllerOperatorDriver::checkOpenShift');
$routes->post('/operator-driver/submit-hm-end', 'PrestartInspection\Mobile\ControllerOperatorDriver::submitHmEnd');
// 2. PROTECTED ROUTES (Requires Login)
// Use Shield's native 'session' filter instead of a custom one
$routes->group('', function ($routes) {

    // Pages
    $routes->get('/admin-dashboard', 'DashboardRender::index');
    $routes->get('/prestart-insepction', 'PrestartInspection\ControllerPSI::index');
    $routes->get('/manpower', 'Manpower\ControllerManpower::index');
    $routes->get('/notification-target', 'Settings\NotificationTarget::index');
    $routes->post('/notification-target/store', 'Settings\NotificationTarget::store');
    $routes->post('/notification-target/update/(:num)', 'Settings\NotificationTarget::update/$1');
    $routes->post('/notification-target/toggle/(:num)', 'Settings\NotificationTarget::toggle/$1');
    $routes->post('/notification-target/delete/(:num)', 'Settings\NotificationTarget::destroy/$1');
    $routes->get('/check-part', 'Settings\CheckPart::index');
    $routes->post('/check-part/store', 'Settings\CheckPart::store');
    $routes->post('/check-part/update/(:num)', 'Settings\CheckPart::update/$1');
    $routes->post('/check-part/delete/(:num)', 'Settings\CheckPart::destroy/$1');
    $routes->get('/equipment-observed-list', 'Settings\EquipmentObservedList::index');
    $routes->post('/equipment-observed-list/store', 'Settings\EquipmentObservedList::store');
    $routes->post('/equipment-observed-list/update/(:num)', 'Settings\EquipmentObservedList::update/$1');
    $routes->post('/equipment-observed-list/delete/(:num)', 'Settings\EquipmentObservedList::destroy/$1');

    // Uploads & Ajax (Protected)
    $routes->post('/preview-psi-record', 'PrestartInspection\PreviewBeforeUpload::index');
    $routes->post('/upload-psi-record', 'PrestartInspection\UploadPSIRecord::index');
    $routes->get('/ajax-datatable/prestartrecord', 'PrestartInspection\ControllerGetData::fetchPSIDetail');
    // $routes->get('/ajax/operator-driver-name-id', 'PrestartInspection\ControllerGetData::fetchMPList');
    $routes->get('/ajax-common-table/psi-by-equipment-type', 'PrestartInspection\ControllerGetData::getSumIssue');
    $routes->get('/ajax-datatable/manpowerlist', 'Manpower\ControllerGetData::fetchManPowerList');
    $routes->get('/ajax-chart/freq-danger-code', 'PrestartInspection\ControllerGetData::fetchGetDangerCodeFreq');
    $routes->get('/ajax-daily-unit-type', 'PrestartInspection\ControllerEmailReport::getData1');
    $routes->get('/ajax/dashboard-data', 'DashboardRender::ajaxData');

    // Testing
    $routes->get('/test-mail', 'TestMail::sendTestEmail');
    $routes->get('/psi-generate-pdf', 'PrestartInspection\ControllerSentReportTest::psiDailyDetailReport');
    $routes->get('/psi-generate-excel', 'PrestartInspection\ControllerGetExcelReport::index');
});

service('auth')->routes($routes);
