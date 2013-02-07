<?php

namespace Liip\MonitorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Liip\Monitor\Check\Runner;
use Liip\MonitorBundle\Helper\PathHelper;
use Liip\Monitor\Check\CheckChain;

class HealthCheckController
{
    protected $healthCheckChain;
    protected $runner;
    protected $pathHelper;

    /**
     * @param \Liip\Monitor\Check\CheckChain $healthCheckChain
     * @param \Liip\Monitor\Check\Runner $runner
     * @param \Liip\MonitorBundle\Helper\PathHelper $pathHelper
     */
    public function __construct(CheckChain $healthCheckChain, Runner $runner, PathHelper $pathHelper)
    {
        $this->healthCheckChain = $healthCheckChain;
        $this->runner = $runner;
        $this->pathHelper = $pathHelper;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $urls = $this->pathHelper->getRoutesJs(array(
            'liip_monitor_run_all_checks' => array(),
            'liip_monitor_run_single_check' => array('checkId' => 'replaceme')
        ));

        $css = $this->pathHelper->getStyleTags(array(
            'bundles/liipmonitor/css/bootstrap/css/bootstrap.min.css',
            'bundles/liipmonitor/css/style.css'
        ));

        $javascript = $this->pathHelper->getScriptTags(array(
            'bundles/liipmonitor/javascript/jquery-1.7.1.min.js',
            'bundles/liipmonitor/javascript/ember-0.9.5.min.js',
            'bundles/liipmonitor/javascript/app.js'
        ));

        // this is a hack to make the bundle template agnostic.
        // URL generation for Assets and Routes is still handled by the framework.
        ob_start();
        include __DIR__ . '/../Resources/views/health/index.html.php';
        $content = ob_get_clean();

        return new Response($content, 200);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->getJsonResponse($this->healthCheckChain->getAvailableChecks());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listGroups()
    {
        return $this->getJsonResponse($this->healthCheckChain->getGroups());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function runAllChecksAction()
    {
        $results = $this->runner->runAllChecks();
        $data = array();
        $globalStatus = 'OK';
        foreach ($results as $id => $result) {
            $tmp = $result->toArray();
            $tmp['service_id'] = $id;

            if ($tmp['status'] > 0) {
                $globalStatus = 'KO';
            }

            $data[] = $tmp;
        }

        return $this->getJsonResponse(array('checks' => $data, 'globalStatus' => $globalStatus));
    }


    /**
     * @param string $checkId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function runSingleCheckAction($checkId)
    {
        $result = $this->runner->runCheckById($checkId)->toArray();
        $result['service_id'] = $checkId;

        return $this->getJsonResponse($result);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function runAllGroupChecksAction()
    {
        $results = $this->runner->runAllChecksByGroup();
        $data = array();

        foreach ($results as $id => $result) {
            $data[$id] = $result->getStatusName();
        }

        return $this->getJsonResponse($data);
    }

    /**
     * @param $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getJsonResponse($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}
