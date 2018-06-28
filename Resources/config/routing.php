<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$controller = function ($service, $method) {
    return sprintf(Kernel::VERSION_ID < 40100 ? '%s:%s' : '%s::%s', $service, $method);
};

$routes = new RouteCollection();

$routes->add('liip_monitor_health_interface', new Route('/', [
    '_controller' => $controller('liip_monitor.health_controller', 'indexAction'),
]));

$routes->add('liip_monitor_list_checks', new Route('/checks', [
    '_controller' => $controller('liip_monitor.health_controller', 'listAction'),
]));

$routes->add('liip_monitor_list_all_checks', new Route('/all_checks', [
    '_controller' => $controller('liip_monitor.health_controller', 'listAllAction'),
]));

$routes->add('liip_monitor_list_groups', new Route('/groups', [
    '_controller' => $controller('liip_monitor.health_controller', 'listGroupsAction'),
]));

$routes->add('liip_monitor_run_all_checks_http_status', new Route('/http_status_checks', [
    '_controller' => $controller('liip_monitor.health_controller', 'runAllChecksHttpStatusAction'),
]));

$routes->add('liip_monitor_run_single_check_http_status', new Route('//http_status_check/{checkId}', [
    '_controller' => $controller('liip_monitor.health_controller', 'runSingleCheckHttpStatusAction'),
]));

$routes->add('liip_monitor_run_all_checks', new Route('/run', [
    '_controller' => $controller('liip_monitor.health_controller', 'runAllChecksAction'),
]));

$routes->add('liip_monitor_run_single_check', new Route('/run/{checkId}', [
    '_controller' => $controller('liip_monitor.health_controller', 'runSingleCheckAction'),
]));

return $routes;
