<?php

namespace Liip\MonitorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController
{
    protected $templating;
    protected $healthCheckChain;
    protected $runner;
    protected $pathHelper;

    public function __construct($templating, $healthCheckChain, $runner, $pathHelper)
    {
        $this->templating = $templating;
        $this->healthCheckChain = $healthCheckChain;
        $this->runner = $runner;
        $this->pathHelper = $pathHelper;
    }

    public function indexAction()
    {
        $urls = $this->pathHelper->getRoutesJs(array(
            'run_all_checks' => array(),
            'run_single_check' => array('check_id' => 'replaceme')
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

        ob_start();
        include __DIR__ . '/../Resources/views/health/index.html.php';
        $content = ob_get_clean();

        return new Response($content, 200);
    }

    public function listAction(Request $request)
    {
        return $this->getJsonResponse($this->healthCheckChain->getAvailableChecks());
    }

    public function runAllChecksAction(Request $request)
    {
        $results = $this->runner->runAllChecks();
        $data = array();
        foreach ($results as $id => $result) {
            $tmp = $result->toArray();
            $tmp['service_id'] = $id;
            $data[] = $tmp;
        }
        return $this->getJsonResponse(array('checks' => $data));
    }

    public function runSingleCheckAction($check_id)
    {
        $result = $this->runner->runCheckById($check_id)->toArray();
        $result['service_id'] = $check_id;
        return $this->getJsonResponse($result);
    }

    protected function getJsonResponse($data)
    {
        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}