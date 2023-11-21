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

use Liip\Monitor\Check\CheckContext;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PreRunCheckEvent extends Event
{
    /**
     * @internal
     */
    public function __construct(private CheckContext $checkContext)
    {
    }

    public function check(): CheckContext
    {
        return $this->checkContext;
    }
}
