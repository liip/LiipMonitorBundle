<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Controller;

use Liip\Monitor\Check\CheckRegistry;
use Liip\Monitor\Check\CheckSuite;
use Liip\Monitor\Result\ResultContext;
use Liip\Monitor\Result\Status;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class OhDearController
{
    public function __construct(
        private CheckRegistry $checks,

        #[Autowire('%env(OH_DEAR_MONITOR_SECRET)%')]
        private string $secret,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if ($this->secret !== $request->headers->get('oh-dear-health-check-secret')) {
            throw new NotFoundHttpException();
        }

        $results = \array_map(
            static function(ResultContext $result) {
                return [
                    'name' => $result->check()->id(),
                    'label' => $result->check()->label(),
                    'status' => match ($result->status()) {
                        Status::SUCCESS => 'ok',
                        Status::WARNING => 'warning',
                        Status::FAILURE => 'failed',
                        Status::SKIP => 'skipped',
                        default => 'crashed',
                    },
                    'notificationMessage' => $result->detail() ?? $result->summary(),
                    'shortSummary' => $result->summary(),
                    'meta' => $result->normalizedContext(),
                ];
            },
            $this->checks()->run()->all(),
        );

        return new JsonResponse([
            'finishedAt' => \time(),
            'checkResults' => $results,
        ]);
    }

    /**
     * Override to customize the check suite used.
     */
    protected function checks(): CheckSuite
    {
        return $this->checks->suite();
    }
}
