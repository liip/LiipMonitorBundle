<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Check\Symfony;

use Liip\Monitor\Check\Symfony\SymfonyVersionCheck;
use Liip\Monitor\Info\Symfony\SymfonyVersionInfo;
use Liip\Monitor\Result;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @group slow
 */
final class SymfonyVersionCheckTest extends TestCase
{
    use CheckTests;

    private static string $stable;
    private static string $latest54;

    public static function checkResultProvider(): iterable
    {
        $stable = self::stable(...);
        $latest54 = self::latest54(...);

        yield [
            fn() => new SymfonyVersionCheck(new SymfonyVersionInfo($stable())),
            fn() => Result::success($stable()),
            'Symfony Version',
        ];

        yield [
            new SymfonyVersionCheck(new SymfonyVersionInfo('5.4.5')),
            Result::warning('5.4.5 - requires a patch update to '.$latest54(), context: ['latest_patch_version' => $latest54()]),
        ];

        yield [
            new SymfonyVersionCheck(new SymfonyVersionInfo('5.3.2')),
            Result::failure('5.3.2 - the 5.3 branch is EOL', context: ['eol_date' => new \DateTimeImmutable('2022-01-01')]),
        ];
    }

    private static function stable(): string
    {
        return self::$stable ??= HttpClient::create()->request('GET', 'https://symfony.com/releases.json')->toArray()['symfony_versions']['stable'];
    }

    private static function latest54(): string
    {
        return self::$latest54 ??= HttpClient::create()->request('GET', 'https://symfony.com/releases/5.4.json')->toArray()['latest_patch_version'];
    }
}
