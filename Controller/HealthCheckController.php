<?php

namespace Liip\MonitorBundle\Controller;

use Liip\MonitorBundle\Helper\ArrayReporter;
use Liip\MonitorBundle\Helper\PathHelper;
use Liip\MonitorBundle\Helper\RunnerManager;
use Liip\MonitorBundle\Helper\StreamedReporter;
use Liip\MonitorBundle\Runner;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HealthCheckController
{
    protected $runnerManager;
    protected $pathHelper;
    protected $template;
    protected $failureStatusCode;
    protected $isLazyRun;

    /**
     * @param $template
     * @param $failureStatusCode
     * @param $isLazyRun
     */
    public function __construct(RunnerManager $runnerManager, PathHelper $pathHelper, $template, $failureStatusCode, $isLazyRun)
    {
        $this->runnerManager = $runnerManager;
        $this->pathHelper = $pathHelper;
        $this->template = $template;
        $this->failureStatusCode = $failureStatusCode;
        $this->isLazyRun = $isLazyRun;
    }

    /**
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $group = $this->getGroup($request);

        $urls = $this->pathHelper->getRoutesJs([
            'liip_monitor_run_all_checks' => ['group' => $group],
            'liip_monitor_stream_all_checks' => ['group' => $group],
            'liip_monitor_run_single_check' => ['checkId' => 'replaceme', 'group' => $group],
        ]);

        $css = $this->pathHelper->getStyleTags([
            'bundles/liipmonitor/css/bootstrap/css/bootstrap.min.css',
            'bundles/liipmonitor/css/style.css',
        ]);

        $javascript = $this->pathHelper->getScriptTags([
            'bundles/liipmonitor/javascript/jquery-1.7.1.min.js',
            'bundles/liipmonitor/javascript/ember-0.9.5.min.js',
            'bundles/liipmonitor/javascript/app.js',
        ]);

        $isLazyRun = $this->isLazyRun ? 1 : 0;

        // this is a hack to make the bundle template agnostic.
        // URL generation for Assets and Routes is still handled by the framework.
        ob_start();
        include $this->template;
        $content = ob_get_clean();

        return new Response($content, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * @return Response
     */
    public function listAction(Request $request)
    {
        $ret = [];

        $runner = $this->getRunner($request);

        foreach ($runner->getChecks() as $alias => $check) {
            $ret[] = $alias;
        }

        return new JsonResponse($ret);
    }

    /**
     * @return JsonResponse
     */
    public function listAllAction()
    {
        $allChecks = [];

        foreach ($this->runnerManager->getRunners() as $group => $runner) {
            foreach ($runner->getChecks() as $alias => $check) {
                $allChecks[$group][] = $alias;
            }
        }

        return new JsonResponse($allChecks);
    }

    /**
     * @return JsonResponse
     */
    public function listGroupsAction()
    {
        $groups = $this->runnerManager->getGroups();

        return new JsonResponse($groups);
    }

    /**
     * @return Response
     */
    public function runAllChecksAction(Request $request)
    {
        $report = $this->runTests($request);

        return new JsonResponse([
            'checks' => $report->getResults(),
            'globalStatus' => $report->getGlobalStatus(),
        ]);
    }

    public function streamAllChecksAction(Request $request)
    {
        return new StreamedResponse(
            function () use ($request) {
                $reporter = new StreamedReporter();
                $runner = $this->getRunner($request);

                $runner->addReporter($reporter);
                $runner->run();
            }
        );
    }

    /**
     * @return Response
     */
    public function runAllChecksHttpStatusAction(Request $request)
    {
        $report = $this->runTests($request);

        return new Response(
            '',
            (ArrayReporter::STATUS_OK === $report->getGlobalStatus() ? 200 : $this->failureStatusCode)
        );
    }

    /**
     * @param string $checkId
     *
     * @return Response
     */
    public function runSingleCheckHttpStatusAction($checkId, Request $request)
    {
        $report = $this->runTests($request, $checkId);

        return new Response(
            '',
            (ArrayReporter::STATUS_OK === $report->getGlobalStatus() ? 200 : $this->failureStatusCode)
        );
    }

    /**
     * @param string $checkId
     *
     * @return Response
     */
    public function runSingleCheckAction($checkId, Request $request)
    {
        $results = $this->runTests($request, $checkId)->getResults();

        return new JsonResponse($results[0]);
    }

    /**
     * @param string|null $checkId
     *
     * @return ArrayReporter
     */
    protected function runTests(Request $request, $checkId = null)
    {
        $reporters = $request->query->get('reporters', []);

        if (!is_array($reporters)) {
            $reporters = [$reporters];
        }

        $reporter = new ArrayReporter();

        $runner = $this->getRunner($request);

        $runner->addReporter($reporter);
        $runner->useAdditionalReporters($reporters);
        $runner->run($checkId);

        return $reporter;
    }

    /**
     * @return Runner
     *
     * @throws \Exception
     */
    private function getRunner(Request $request)
    {
        $group = $this->getGroup($request);

        $runner = $this->runnerManager->getRunner($group);

        if ($runner) {
            return $runner;
        }

        throw new \RuntimeException(sprintf('Unknown check group "%s"', $group));
    }

    /**
     * @return string
     */
    private function getGroup(Request $request)
    {
        return $request->query->get('group') ?: $this->runnerManager->getDefaultGroup();
    }

    /**
     * @return Response
     */
    public function listReportersAction()
    {
        return new JsonResponse($this->runnerManager->getReporters());
    }
}
