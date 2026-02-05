<?php

use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();
$routes->addCollection(
    $loader->import('@LiipMonitorBundle/Resources/config/routing.yaml')
);

return $routes;
