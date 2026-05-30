<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// $routes->get('/', 'Home::index');
$routes->get('/', 'DashboardRender::index');
$routes->get('/prestart-insepction', 'PrestartInspection\ControllerPSI::index');
$routes->get('/manpower', 'Manpower\ControllerManpower::index');

// uploads and preview
$routes->post('/preview-psi-record', 'PrestartInspection\PreviewBeforeUpload::index'); // preview the file
$routes->post('/upload-psi-record', 'PrestartInspection\UploadPSIRecord::index'); // upload the file

// ajax routes
$routes->get('/ajax-datatable/prestartrecord', 'PrestartInspection\ControllerGetData::fetchPSIDetail');
$routes->get('/ajax-common-table/psi-by-equipment-type', 'PrestartInspection\ControllerGetData::getSumIssue');
$routes->get('/ajax-datatable/manpowerlist', 'Manpower\ControllerGetData::fetchManPowerList');

// ajax routes - charting
$routes->get('/ajax-chart/freq-danger-code', 'PrestartInspection\ControllerGetData::fetchGetDangerCodeFreq');


service('auth')->routes($routes);
