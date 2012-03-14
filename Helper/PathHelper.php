<?php

namespace Liip\MonitorBundle\Helper;

class PathHelper
{
    protected $assetsHelper;
    protected $routerHelper;

    public function __construct($container)
    {
        $this->assetsHelper = $container->get('templating.helper.assets');
        $this->routerHelper = $container->get('templating.helper.router');
    }

    public function generateRoutes($rotues)
    {
        $routes = array();
        foreach ($rotues as $route => $params) {
            $routes[] = sprintf('api.%s = "%s";', $route, $this->routerHelper->generate($route, $params));
        }
        return $routes;
    }

    public function getRoutesJs($routes)
    {
        $script = '<script type="text/javascript" charset="utf-8">';
        $script .= 'var api = {};';
        $script .= implode("\n", $this->generateRoutes($routes));
        $script .= '</script>';
        return $script;
    }

    public function getScriptTags($paths)
    {
        $ret = '';
        foreach ($paths as $path) {
            $ret .= sprintf('<script type="text/javascript" charset="utf-8" src="%s"></script>%s', $this->assetsHelper->getUrl($path), "\n");
        }
        return $ret;
    }

    public function getStyleTags($paths)
    {
        $ret = '';
        foreach ($paths as $path) {
            $ret .= sprintf('<link rel="stylesheet" href="%s" type="text/css">%s', $this->assetsHelper->getUrl($path), "\n");
        }
        return $ret;
    }
}