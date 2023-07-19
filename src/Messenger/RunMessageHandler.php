<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Messenger;

use Liip\Monitor\Check\CheckRegistry;
use Liip\Monitor\Result\ResultContext;
use Liip\Monitor\Result\ResultSet;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class RunMessageHandler
{
    public function __construct(private CheckRegistry $checks)
    {
    }

    public function runSuite(RunCheckSuite $message): ResultSet
    {
        return $this->checks->suite($message->suite)->run($message->cache);
    }

    public function runCheck(RunCheck $message): ResultContext
    {
        return $this->checks->get($message->id)->run($message->cache);
    }

    public function runChecks(RunChecks $message): ResultSet
    {
        $results = [];

        foreach ($message->ids as $id) {
            $results[] = $this->checks->get($id)->run($message->cache);
        }

        return new ResultSet($results);
    }
}
