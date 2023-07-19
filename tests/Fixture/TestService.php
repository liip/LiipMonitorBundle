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

use Liip\Monitor\Check\CheckRegistry;
use Liip\Monitor\Check\CheckSuite;
use Liip\Monitor\Info\Php\ApcuCacheInfo;
use Liip\Monitor\Info\Php\OpCacheInfo;
use Liip\Monitor\Info\Php\PhpInfo;
use Liip\Monitor\Info\Php\PhpVersionInfo;
use Liip\Monitor\Info\Symfony\SymfonyVersionInfo;
use Liip\Monitor\System;
use Liip\Monitor\System\LinuxSystem;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TestService
{
    public function __construct(
        public readonly CheckRegistry $checkRegistry,
        public readonly CheckSuite $checks,
        public readonly CheckSuite $fooChecks,
        public readonly CheckSuite $barChecks,
        public readonly CheckSuite $bazChecks,
        public readonly System $system,
        public readonly LinuxSystem $linuxSystem,
        public readonly PhpInfo $phpInfo,
        public readonly ApcuCacheInfo $apcuCacheInfo,
        public readonly OpCacheInfo $opCacheInfo,
        public readonly PhpVersionInfo $phpVersionInfo,
        public readonly SymfonyVersionInfo $symfonyVersionInfo,
    ) {
    }
}
