<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
enum Status: string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case FAILURE = 'failure';
    case ERROR = 'error';
    case UNKNOWN = 'unknown';
    case SKIP = 'skip';
}
