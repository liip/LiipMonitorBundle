<?php

namespace Liip\MonitorBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PathHelper
{
    protected $assetsHelper;
    protected $routerHelper;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        // symfony3 does not define templating.helper.assets unless php templating is included
        $this->assetsHelper = $container->has('templating.helper.assets') ?
            $container->get('templating.helper.assets') : $container->get('assets.packages');

        // symfony3 does not define templating.helper.router unless php templating is included
        $this->routerHelper = $container->has('templating.helper.router') ?
            $container->get('templating.helper.router') : $container->get('router');
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
