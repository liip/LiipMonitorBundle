<?php

namespace Liip\MonitorBundle\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PathHelper
{
    protected $assetsHelper;
    protected $routerHelper;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $symfony_version = \Symfony\Component\HttpKernel\Kernel::VERSION;
        if ($container->has('templating.helper.assets') && version_compare($symfony_version, '3.0.0') === -1) {
            $this->assetsHelper = $container->get('templating.helper.assets');
        } else {
            $this->assetsHelper = $container->get('assets.packages');
        }
        $this->routerHelper = $container->get('router');
    }

    /**
     * @param array $routes
     *
     * @return array
     */
    public function generateRoutes(array $routes)
    {
        $ret = array();
        foreach ($routes as $route => $params) {
            $ret[] = sprintf('api.%s = "%s";', $route, $this->routerHelper->generate($route, $params));
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
