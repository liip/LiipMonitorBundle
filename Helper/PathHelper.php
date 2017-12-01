<?php

namespace Liip\MonitorBundle\Helper;

class PathHelper
{
    protected $assetsHelper;
    protected $routerHelper;

    public function __construct($assetsHelper, $routerHelper)
    {
        $this->assetsHelper = $assetsHelper;
        $this->routerHelper = $routerHelper;
    }

    /**
     * @param array $routes
     *
     * @return array
     */
    public function generateRoutes(array $routes)
    {
        $ret = array();

        if (!method_exists($this->routerHelper, 'path')) {
            // symfony 2.7 and lower don't have the path method, BC layer
            foreach ($routes as $route => $params) {
                $ret[] = sprintf('api.%s = "%s";', $route, $this->routerHelper->generate($route, $params));
            }

            return $ret;
        }

        foreach ($routes as $route => $params) {
            $ret[] = sprintf('api.%s = "%s";', $route, $this->routerHelper->path($route, $params));
        }

        return $ret;
    }

    /**
     * @param array $routes
     *
     * @return string
     */
    public function getRoutesJs(array $routes)
    {
        $script = '<script type="text/javascript" charset="utf-8">';
        $script .= "var api = {};\n";
        $script .= implode("\n", $this->generateRoutes($routes));
        $script .= '</script>';

        return $script;
    }

    /**
     * @param string[] $paths
     *
     * @return string
     */
    public function getScriptTags(array $paths)
    {
        $ret = '';
        foreach ($paths as $path) {
            $ret .= sprintf('<script type="text/javascript" charset="utf-8" src="%s"></script>%s', $this->assetsHelper->getUrl($path), "\n");
        }

        return $ret;
    }

    /**
     * @param string[] $paths
     *
     * @return string
     */
    public function getStyleTags(array $paths)
    {
        $ret = '';
        foreach ($paths as $path) {
            $ret .= sprintf('<link rel="stylesheet" href="%s" type="text/css">%s', $this->assetsHelper->getUrl($path), "\n");
        }

        return $ret;
    }
}
