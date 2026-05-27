<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// $routes->get('/', 'Home::index');
$routes->get('/', 'DashboardRender::index');
$routes->get('/prestart-insepction', 'PrestartInspection\ControllerPSI::index');

service('auth')->routes($routes);
