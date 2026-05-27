<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// $routes->get('/', 'Home::index');
$routes->get('/', 'DashboardRender::index');
$routes->get('/prestart-insepction', 'PrestartInspection\ControllerPSI::index');
$routes->get('/ajax-datatable/prestartrecord', 'PrestartInspection\ControllerGetData::fetchPSIDetail');

service('auth')->routes($routes);
