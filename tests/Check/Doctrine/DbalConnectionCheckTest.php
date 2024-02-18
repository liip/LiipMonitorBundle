<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Check\Doctrine;

use Doctrine\Persistence\ConnectionRegistry;
use Liip\Monitor\Check\Doctrine\DbalConnectionCheck;
use Liip\Monitor\Result;
use Liip\Monitor\Result\Status;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DbalConnectionCheckTest extends KernelTestCase
{
    #[Test]
    public function can_run(): void
    {
        $check = new DbalConnectionCheck(self::getContainer()->get('doctrine'), 'default');

        $this->assertSame('DBAL Connection "default"', (string) $check);

        $this->assertSame(Status::SUCCESS, $check->run()->status());
    }

    #[Test]
    public function invalid_connection(): void
    {
        $connectionRegistry = $this->createMock(ConnectionRegistry::class);
        $connectionRegistry->expects($this->once())
            ->method('getConnection')
            ->with('invalid')
            ->willReturn(new \stdClass());

        $check = new DbalConnectionCheck($connectionRegistry, 'invalid');

        $this->assertEquals(Result::failure('Connection "invalid" is not a Doctrine DBAL connection.'), $check->run());
    }
}
