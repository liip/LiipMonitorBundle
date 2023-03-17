<?php

namespace Liip\MonitorBundle\Controller;

use Liip\MonitorBundle\Helper\ArrayReporter;
use Liip\MonitorBundle\Helper\PathHelper;
use Liip\MonitorBundle\Helper\RunnerManager;
use Liip\MonitorBundle\Runner;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController
{
    protected $runnerManager;
    protected $pathHelper;
    protected $template;
    protected $failureStatusCode;

    /**
     * @param $template
     * @param $failureStatusCode
     */
    public function __construct(RunnerManager $runnerManager, PathHelper $pathHelper, $template, $failureStatusCode)
    {
        $this->runnerManager = $runnerManager;
        $this->pathHelper = $pathHelper;
        $this->template = $template;
        $this->failureStatusCode = $failureStatusCode;
    }

    public function indexAction(Request $request): Response
    {
        $group = $this->getGroup($request);

        $urls = $this->pathHelper->getRoutesJs([
            'liip_monitor_run_all_checks' => ['group' => $group],
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

        // this is a hack to make the bundle template agnostic.
        // URL generation for Assets and Routes is still handled by the framework.
        ob_start();
        include $this->template;
        $content = ob_get_clean();

        return new Response($content, 200, ['Content-Type' => 'text/html']);
    }

    public function listAction(Request $request): JsonResponse
    {
        $ret = [];

        $runner = $this->getRunner($request);

        foreach ($runner->getChecks() as $alias => $check) {
            $ret[] = $alias;
        }

        return new JsonResponse($ret);
    }

    public function listAllAction(): JsonResponse
    {
        $allChecks = [];

        foreach ($this->runnerManager->getRunners() as $group => $runner) {
            foreach ($runner->getChecks() as $alias => $check) {
                $allChecks[$group][] = $alias;
            }
        }

        return new JsonResponse($allChecks);
    }

    public function listGroupsAction(): JsonResponse
    {
        $groups = $this->runnerManager->getGroups();

        return new JsonResponse($groups);
    }

    public function runAllChecksAction(Request $request): JsonResponse
    {
        $report = $this->runTests($request);

        return new JsonResponse([
            'checks' => $report->getResults(),
            'globalStatus' => $report->getGlobalStatus(),
        ]);
    }

    public function runAllChecksHttpStatusAction(Request $request): Response
    {
        $report = $this->runTests($request);

        return new Response(
            '',
            (ArrayReporter::STATUS_OK === $report->getGlobalStatus() ? 200 : $this->failureStatusCode)
        );
    }

    /**
     * @param string $checkId
     */
    public function runSingleCheckHttpStatusAction($checkId, Request $request): Response
    {
        $report = $this->runTests($request, $checkId);

        return new Response(
            '',
            (ArrayReporter::STATUS_OK === $report->getGlobalStatus() ? 200 : $this->failureStatusCode)
        );
    }

    /**
     * @param string $checkId
     */
    public function runSingleCheckAction($checkId, Request $request): JsonResponse
    {
        $results = $this->runTests($request, $checkId)->getResults();

        return new JsonResponse($results[0]);
    }

    /**
     * @param string|null $checkId
     */
    protected function runTests(Request $request, $checkId = null): ArrayReporter
    {
        $reporters = $request->query->all('reporters') ?? [];

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
     * @throws \Exception
     */
    private function getRunner(Request $request): Runner
    {
        $group = $this->getGroup($request);

        $runner = $this->runnerManager->getRunner($group);

        if ($runner) {
            return $runner;
        }

        throw new \RuntimeException(sprintf('Unknown check group "%s"', $group));
    }

    private function getGroup(Request $request): string
    {
        return $request->query->get('group') ?: $this->runnerManager->getDefaultGroup();
    }

    public function listReportersAction(): JsonResponse
    {
        return new JsonResponse($this->runnerManager->getReporters());
    }
}
