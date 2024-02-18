<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Check\Php;

use Liip\Monitor\Check\Php\ComposerAuditCheck;
use Liip\Monitor\Result;
use Liip\Monitor\Result\Status;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[Group('slow')]
final class ComposerAuditCheckTest extends TestCase
{
    #[Test]
    public function successful_check(): void
    {
        $check = new ComposerAuditCheck(__DIR__.'/../../Fixture/project1');

        $this->assertSame('Composer Security Audit', (string) $check);

        $result = $check->run();

        $this->assertEquals(Result::success('No advisories'), $result);
    }

    #[Test]
    public function failed_check(): void
    {
        $check = new ComposerAuditCheck(__DIR__.'/../../Fixture/project2');

        $result = $check->run();

        $this->assertSame(Status::FAILURE, $result->status());
        $this->assertSame('2 advisories', $result->summary());
        $this->assertSame('symfony/security-http, symfony/twig-bridge', $result->detail());
        $this->assertCount(2, $result->context()['advisories']);
    }
}
