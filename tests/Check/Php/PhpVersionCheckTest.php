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

use Liip\Monitor\Check\Php\PhpVersionCheck;
use Liip\Monitor\Info\Php\PhpVersionInfo;
use Liip\Monitor\Result;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[Group('slow')]
final class PhpVersionCheckTest extends TestCase
{
    use CheckTests;

    private static array $stable;

    public static function checkResultProvider(): iterable
    {
        $stable = self::stable(...);

        yield [
            fn() => new PhpVersionCheck(new PhpVersionInfo($stable()['version'])),
            fn() => Result::success($stable()['version']),
            'PHP Version',
        ];

        yield [
            new PhpVersionCheck(new PhpVersionInfo('8.2.6')),
            fn() => Result::warning('PHP 8.2.6 requires a patch update to '.$stable()['version'], context: [
                'latest_patch_version' => $stable()['version'],
                'latest_patch_date' => new \DateTimeImmutable($stable()['date']),
            ]),
        ];

        yield [
            new PhpVersionCheck(new PhpVersionInfo('7.4.5')),
            Result::failure('PHP 7.4 is EOL', context: ['eol_date' => new \DateTimeImmutable('2022-11-28')]),
        ];
    }

    private static function stable(): array
    {
        return self::$stable ??= HttpClient::create()->request('GET', 'https://www.php.net/releases/active')->toArray()['8']['8.2'];
    }
}
