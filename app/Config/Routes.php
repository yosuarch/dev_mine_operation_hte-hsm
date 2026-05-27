<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// $routes->get('/', 'Home::index');
$routes->get('/', 'DashboardRender::index');
$routes->get('/prestart-insepction', 'PrestartInspection\ControllerPSI::index');
$routes->get('/manpower', 'Manpower\ControllerManpower::index');



// ajax routes
$routes->get('/ajax-datatable/prestartrecord', 'PrestartInspection\ControllerGetData::fetchPSIDetail');
$routes->get('/ajax-datatable/manpowerlist', 'Manpower\ControllerGetData::fetchManPowerList');

service('auth')->routes($routes);
