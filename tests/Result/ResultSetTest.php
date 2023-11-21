<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Result;

use Liip\Monitor\Check\CallbackCheck;
use Liip\Monitor\Check\CheckContext;
use Liip\Monitor\Result;
use Liip\Monitor\Result\ResultContext;
use Liip\Monitor\Result\ResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ResultSetTest extends TestCase
{
    /**
     * @test
     */
    public function json_serialize(): void
    {
        $results = new ResultSet([
            new ResultContext(
                new CheckContext(new CallbackCheck('first', fn() => null)),
                Result::success('summary 1', 'detail 1', ['context' => 1]),
                0.1
            ),
            new ResultContext(
                new CheckContext(new CallbackCheck('second', fn() => null)),
                Result::success('summary 2', 'detail 2', ['context' => 2]),
                0.1
            ),
        ]);

        $this->assertSame(
            [
                'results' => [
                    [
                        'check' => ['label' => 'first', 'id' => 'dde84cbb'],
                        'status' => 'success',
                        'summary' => 'summary 1',
                        'detail' => 'detail 1',
                        'context' => ['context' => 1],
                        'duration' => 0.1,
                    ],
                    [
                        'check' => ['label' => 'second', 'id' => '001341e4'],
                        'status' => 'success',
                        'summary' => 'summary 2',
                        'detail' => 'detail 2',
                        'context' => ['context' => 2],
                        'duration' => 0.1,
                    ],
                ],
                'duration' => 0.2,
            ],
            \json_decode(\json_encode($results), true),
        );
    }
}
