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
use Liip\Monitor\AsCheck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsCheck('baz', label: 'Custom Check Service 4', id: 'custom_check_service_4')]
final class CheckService4 extends AbstractCheck
{
    public function check(): ResultInterface
    {
        return new Success();
    }
}
