<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests;

use Liip\Monitor\System;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SystemTest extends TestCase
{
    #[Test]
    public function is_reboot_required(): void
    {
        $system = $this->create();

        $this->expectException(\BadMethodCallException::class);

        $system->isRebootRequired();
    }

    #[Test]
    public function load_averages(): void
    {
        $averages = $this->create()->loadAverages();

        $this->assertCount(3, $averages);
        $this->assertSame([0, 1, 2], \array_keys($averages));
        $this->assertLessThanOrEqual(1.0, $averages[0]->decimal());
        $this->assertLessThanOrEqual(1.0, $averages[1]->decimal());
        $this->assertLessThanOrEqual(1.0, $averages[2]->decimal());
        $this->assertGreaterThanOrEqual(0, $averages[0]->decimal());
        $this->assertGreaterThanOrEqual(0, $averages[1]->decimal());
        $this->assertGreaterThanOrEqual(0, $averages[2]->decimal());
    }

    #[Test]
    public function disk(): void
    {
        $disk = $this->create()->disk();

        $this->assertSame($disk->free()->value() + $disk->used()->value(), $disk->total()->value());
        $this->assertGreaterThanOrEqual(0, $disk->percentUsed()->decimal());
        $this->assertLessThanOrEqual(1.0, $disk->percentUsed()->decimal());
    }

    #[Test]
    public function memory(): void
    {
        $system = $this->create();

        $this->expectException(\BadMethodCallException::class);

        $system->memory();
    }

    #[Test]
    public function stringable(): void
    {
        $this->assertSame('Linux', (string) $this->create());
    }

    #[Test]
    public function webserver(): void
    {
        $this->assertSame('n/a', (string) $this->create()->webserver());
    }

    protected function create(): System
    {
        return new System();
    }
}
