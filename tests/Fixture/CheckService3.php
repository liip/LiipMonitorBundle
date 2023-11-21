<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Fixture;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CheckService3 extends AbstractCheck
{
    public function check(): ResultInterface
    {
        return new Success();
    }
}
