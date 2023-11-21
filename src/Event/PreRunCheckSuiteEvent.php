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
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PreRunCheckSuiteEvent extends Event
{
    /**
     * @internal
     */
    public function __construct(private CheckSuite $checks)
    {
    }

    public function checks(): CheckSuite
    {
        return $this->checks;
    }
}
