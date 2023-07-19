<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Event;

use Liip\Monitor\Check\CheckSuite;
use Liip\Monitor\Result\ResultSet;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PostRunCheckSuiteEvent extends Event
{
    /**
     * @internal
     */
    public function __construct(private CheckSuite $checkSuite, private ResultSet $results)
    {
    }

    public function results(): ResultSet
    {
        return $this->results;
    }

    public function checks(): CheckSuite
    {
        return $this->checkSuite;
    }
}
