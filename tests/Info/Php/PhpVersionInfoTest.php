<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Info\Php;

use Liip\Monitor\Info\Php\PhpVersionInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @group slow
 */
final class PhpVersionInfoTest extends TestCase
{
    /**
     * @test
     */
    public function system_version(): void
    {
        $activeVersions = HttpClient::create()->request('GET', 'https://www.php.net/releases/active')->toArray();

        if (!isset($activeVersions[(string) \PHP_MAJOR_VERSION][\sprintf('%s.%s', \PHP_MAJOR_VERSION, \PHP_MINOR_VERSION)])) {
            $this->markTestSkipped('No active versions found for current PHP version.');
        }

        $info = new PhpVersionInfo();

        $expected = \sprintf('%d.%d.%d', \PHP_MAJOR_VERSION, \PHP_MINOR_VERSION, \PHP_RELEASE_VERSION);

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
        $info->latestPatchReleased();
        $info->isMinorUpdateRequired();
        $info->isMajorUpdateRequired();
        $info->nextMinorVersion();
        $info->nextMinorVersion();
    }

    /**
     * @test
     */
    public function specific_version(): void
    {
        $info = new PhpVersionInfo('7.3.2');

        $this->assertSame('7.3.2', (string) $info);
        $this->assertTrue($info->isEol());
        $this->assertFalse($info->isMaintained());
        $this->assertFalse($info->isStable());
        $this->assertFalse($info->isSecurityOnly());
        $this->assertSame('2018-12-06', $info->releasedOn()->format('Y-m-d'));
        $this->assertSame('2021-12-06', $info->supportUntil()->format('Y-m-d'));
        $this->assertSame('2020-12-06', $info->activeSupportUntil()->format('Y-m-d'));
        $this->assertSame('2021-12-06', $info->securitySupportUntil()->format('Y-m-d'));
        $this->assertTrue($info->isMinorUpdateRequired());
        $this->assertTrue($info->isMajorUpdateRequired());
        $this->assertSame('7.4.0', (string) $info->nextMinorVersion());
        $this->assertSame('8.0.0', (string) $info->nextMajorVersion());
    }
}
