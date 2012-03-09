<?php

namespace Liip\MonitorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController
{
    protected $runner;

    public function __construct($templating, $healthCheckChain, $runner)
    {
        $this->templating = $templating;
        $this->healthCheckChain = $healthCheckChain;
        $this->runner = $runner;
    }

    public function indexAction()
    {
        return $this->templating->renderResponse('LiipMonitorBundle:health:index.html.php');
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