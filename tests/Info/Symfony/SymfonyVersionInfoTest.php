<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Info\Symfony;

use Liip\Monitor\Info\Symfony\SymfonyVersionInfo;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[Group('slow')]
final class SymfonyVersionInfoTest extends TestCase
{
    #[Test]
    public function installed_version(): void
    {
        $info = new SymfonyVersionInfo();

        $expected = \sprintf('%d.%d.%d', Kernel::MAJOR_VERSION, Kernel::MINOR_VERSION, Kernel::RELEASE_VERSION);

        self::assertSame($expected, (string) $info);
        self::assertSame($expected, $info->currentVersion());

        $info->isEol();
        $info->isMaintained();
        $info->isStable();
        $info->isSecurityOnly();
        $info->releasedOn();
        $info->supportUntil();
        $info->activeSupportUntil();
        $info->securitySupportUntil();
        $info->latestPatchVersion();
        $info->isPatchUpdateRequired();
        // $info->latestPatchReleased();
        $info->isMinorUpdateRequired();
        $info->isMajorUpdateRequired();
        $info->nextMinorVersion();
        $info->nextMinorVersion();
        $info->isLts();
        $info->isReleased();
        $info->isLatest();
    }

    #[Test]
    public function specific_version(): void
    {
        $info = new SymfonyVersionInfo('4.3.2');

        $this->assertSame('4.3.2', (string) $info);
        $this->assertTrue($info->isEol());
        $this->assertFalse($info->isMaintained());
        $this->assertFalse($info->isStable());
        $this->assertFalse($info->isSecurityOnly());
        $this->assertSame('2019-05-01', $info->releasedOn()->format('Y-m-d'));
        $this->assertSame('2020-07-01', $info->supportUntil()->format('Y-m-d'));
        $this->assertSame('2020-01-01', $info->activeSupportUntil()->format('Y-m-d'));
        $this->assertSame('2020-07-01', $info->securitySupportUntil()->format('Y-m-d'));
        $this->assertSame('4.3.11', $info->latestPatchVersion());
        $this->assertTrue($info->isPatchUpdateRequired());
        $this->assertTrue($info->isMinorUpdateRequired());
        $this->assertTrue($info->isMajorUpdateRequired());
        $this->assertSame('4.4.0', (string) $info->nextMinorVersion());
        $this->assertSame('5.0.0', (string) $info->nextMajorVersion());
        $this->assertFalse($info->isLts());
        $this->assertFalse($info->isLatest());
        $this->assertTrue($info->isReleased());
    }
}
