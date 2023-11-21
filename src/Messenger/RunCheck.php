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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunCheck extends RunMessage
{
    /**
     * @param string $id The check ID to run
     */
    public function __construct(
        public readonly string $id,
        bool $cache = true,
    ) {
        parent::__construct($cache);
    }
}
