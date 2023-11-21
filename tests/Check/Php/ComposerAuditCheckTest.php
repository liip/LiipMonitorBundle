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
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @group slow
 */
final class ComposerAuditCheckTest extends TestCase
{
    /**
     * @test
     */
    public function can_run(): void
    {
        $check = new ComposerAuditCheck(\dirname(__DIR__, 3));

        $this->assertSame('Composer Security Audit', (string) $check);

        $result = $check->run();

        if (Status::FAILURE === $result->status()) {
            $this->assertSame(\sprintf('%s advisories', \count($result->context()['advisories'])), $result->summary());

            return;
        }

        $this->assertEquals(Result::success('No advisories'), $result);
    }
}
