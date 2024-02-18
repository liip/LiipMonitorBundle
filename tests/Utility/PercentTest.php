<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Utility;

use Liip\Monitor\Utility\Percent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PercentTest extends TestCase
{
    public static function formatsProvider(): iterable
    {
        yield [fn() => Percent::from(5), 0.05, 5.0, '5%', '5.00%'];
        yield [fn() => Percent::from(5.2), 0.052, 5.2, '5%', '5.20%'];
        yield [fn() => Percent::from(5.6), 0.056, 5.6, '6%', '5.60%'];
        yield [fn() => Percent::fromDecimal(0.05), 0.05, 5.0, '5%', '5.00%'];
        yield [fn() => Percent::from(-5), -0.05, -5.0, '-5%', '-5.00%'];
        yield [fn() => Percent::fromDecimal(-0.05), -0.05, -5.0, '-5%', '-5.00%'];
        yield [fn() => Percent::fromDecimal(0.53678), 0.53678, 53.678, '54%', '53.68%'];
        yield [fn() => Percent::calculate(6, 4), 1.5, 150.0, '150%', '150.00%'];
        yield [fn() => Percent::calculate(3, 62), 0.04839, 4.83871, '5%', '4.84%'];
        yield [fn() => Percent::from('5'), 0.05, 5.0, '5%', '5.00%'];
        yield [fn() => Percent::from('5.2'), 0.052, 5.2, '5%', '5.20%'];
        yield [fn() => Percent::from('5.6'), 0.056, 5.6, '6%', '5.60%'];
        yield [fn() => Percent::from('5.2%'), 0.052, 5.2, '5%', '5.20%'];
        yield [fn() => Percent::from('5  %'), 0.05, 5.0, '5%', '5.00%'];
        yield [fn() => Percent::from('   5  %   '), 0.05, 5.0, '5%', '5.00%'];
    }

    #[Test]
    #[DataProvider('formatsProvider')]
    public function formats(callable $p, $expectedDecimal, $expectedPercentage, $expectedString, $expectedFormat): void
    {
        $p = $p();

        $this->assertSame(\round($expectedDecimal, 5), \round($p->decimal(), 5));
        $this->assertSame(\round($expectedPercentage, 5), \round($p->percentage(), 5));
        $this->assertSame($expectedString, (string) $p);
        $this->assertSame($expectedFormat, $p->format(2));
    }

    #[Test]
    public function can_constrain(): void
    {
        $this->assertSame(5.0, Percent::from(5)->constrain()->percentage());
        $this->assertSame(0.0, Percent::from(-5)->constrain()->percentage());
        $this->assertSame(100.0, Percent::from(500)->constrain()->percentage());
        $this->assertSame(100.0, Percent::from(500)->constrainUpper()->percentage());
        $this->assertSame(-5.0, Percent::from(-5)->constrainUpper()->percentage());
        $this->assertSame(0.0, Percent::from(-5)->constrainLower()->percentage());
        $this->assertSame(120.0, Percent::from(120)->constrainLower()->percentage());
    }

    #[Test]
    public function comparisons(): void
    {
        $this->assertTrue(Percent::from(5)->isEqualTo(Percent::from(5)));
        $this->assertFalse(Percent::from(5)->isEqualTo(Percent::from(4)));
        $this->assertTrue(Percent::from(5)->isGreaterThan(Percent::from(4)));
        $this->assertFalse(Percent::from(5)->isGreaterThan(Percent::from(5)));
        $this->assertTrue(Percent::from(5)->isGreaterThanOrEqualTo(Percent::from(5)));
        $this->assertFalse(Percent::from(5)->isGreaterThanOrEqualTo(Percent::from(6)));
        $this->assertTrue(Percent::from(5)->isLessThan(Percent::from(6)));
        $this->assertFalse(Percent::from(5)->isLessThan(Percent::from(5)));
        $this->assertTrue(Percent::from(5)->isLessThanOrEqualTo(Percent::from(5)));
        $this->assertFalse(Percent::from(5)->isLessThanOrEqualTo(Percent::from(4)));
    }

    #[Test]
    public function calculate(): void
    {
        $this->assertSame(0.5, Percent::calculate(5, 10)->decimal());
        $this->assertSame(0.0, Percent::calculate(5, 0, 0)->decimal());
        $this->assertSame(10.0, Percent::calculate(5, 0, 10)->decimal());

        $this->expectException(\DivisionByZeroError::class);

        Percent::calculate(5, 0);
    }

    #[Test]
    #[DataProvider('invalidStringProvider')]
    public function from_percentage_invalid_string(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Percent::from($value);
    }

    public static function invalidStringProvider(): iterable
    {
        yield ['foo'];
        yield ['foo%'];
        yield ['7@'];
    }
}
