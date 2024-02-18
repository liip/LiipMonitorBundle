<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\System;

use Liip\Monitor\System\LinuxSystem;
use Liip\Monitor\Tests\RequiresLinux;
use Liip\Monitor\Tests\SystemTest;
use PHPUnit\Framework\Attributes\Test;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LinuxSystemTest extends SystemTest
{
    use RequiresLinux;

    #[Test]
    public function stringable(): void
    {
        $this->assertStringContainsString('Ubuntu', (string) $this->create());
    }

    #[Test]
    public function is_reboot_required(): void
    {
        $this->assertSame(\file_exists('/var/run/reboot-required'), $this->create()->isRebootRequired());
    }

    #[Test]
    public function memory(): void
    {
        $memory = $this->create()->memory();

        $this->assertSame($memory->free()->value() + $memory->used()->value(), $memory->total()->value());
        $this->assertGreaterThanOrEqual(0, $memory->percentUsed()->decimal());
        $this->assertLessThanOrEqual(1.0, $memory->percentUsed()->decimal());
    }

    #[Test]
    public function cached_memory(): void
    {
        $memory = $this->create()->cachedMemory();

        $this->assertSame($memory->free()->value() + $memory->used()->value(), $memory->total()->value());
        $this->assertGreaterThanOrEqual(0, $memory->percentUsed()->decimal());
        $this->assertLessThanOrEqual(1.0, $memory->percentUsed()->decimal());
    }

    #[Test]
    public function buffered_memory(): void
    {
        $memory = $this->create()->bufferedMemory();

        $this->assertSame($memory->free()->value() + $memory->used()->value(), $memory->total()->value());
        $this->assertGreaterThanOrEqual(0, $memory->percentUsed()->decimal());
        $this->assertLessThanOrEqual(1.0, $memory->percentUsed()->decimal());
    }

    #[Test]
    public function swap_memory(): void
    {
        $memory = $this->create()->swapMemory();

        $this->assertSame($memory->free()->value() + $memory->used()->value(), $memory->total()->value());
        $this->assertGreaterThanOrEqual(0, $memory->percentUsed()->decimal());
        $this->assertLessThanOrEqual(1.0, $memory->percentUsed()->decimal());
    }

    protected function create(): LinuxSystem
    {
        return new LinuxSystem();
    }
}
