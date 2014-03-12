<?php

namespace Liip\MonitorBundle\Controller;

use Liip\MonitorBundle\Helper\ArrayReporter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ZendDiagnostics\Runner\Runner;
use Liip\MonitorBundle\Helper\PathHelper;

class HealthCheckController
{
    protected $runner;
    protected $pathHelper;

    /**
     * @param Runner     $runner
     * @param PathHelper $pathHelper
     */
    public function __construct(Runner $runner, PathHelper $pathHelper)
    {
        $this->runner = $runner;
        $this->pathHelper = $pathHelper;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request  $request
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

        return new Response($content, 200, array('Content-Type' => 'text/html'));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $ret = array();

        foreach ($this->runner->getChecks() as $alias => $check) {
            $ret[] = $alias;
        }

        return new JsonResponse($ret);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function runAllChecksAction()
    {
        $report = $this->runTests();

        return new JsonResponse(array(
            'checks' => $report->getResults(),
            'globalStatus' => $report->getGlobalStatus()
        ));
    }

    /**
     * @param  string                                     $checkId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function runSingleCheckAction($checkId)
    {
        $results = $this->runTests($checkId)->getResults();

        return new JsonResponse($results[0]);
    }

    /**
     * @param  string|null   $checkId
     * @return ArrayReporter
     */
    protected function runTests($checkId = null)
    {
        $reporter = new ArrayReporter();
        $this->runner->addReporter($reporter);
        $this->runner->run($checkId);

        return $reporter;
    }
}
