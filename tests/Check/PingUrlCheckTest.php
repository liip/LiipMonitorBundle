<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Check;

use Liip\Monitor\Check\PingUrlCheck;
use Liip\Monitor\Result;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PingUrlCheckTest extends TestCase
{
    use CheckTests;

    public static function checkResultProvider(): iterable
    {
        yield [
            new PingUrlCheck(
                'https://example.com',
                httpClient: new MockHttpClient(new MockResponse(info: ['total_time' => 0.1]))
            ),
            Result::success('100ms'),
            'Ping https://example.com',
        ];

        yield [
            new PingUrlCheck(
                'https://example.com',
                httpClient: new MockHttpClient(new MockResponse(info: ['http_code' => 204, 'total_time' => 0.1]))
            ),
            Result::success('100ms'),
        ];

        yield [
            new PingUrlCheck(
                'https://example.com',
                httpClient: new MockHttpClient(new MockResponse(info: ['http_code' => 404]))
            ),
            Result::failure(
                '404: Not Found',
                'Expected successful status code, got 404',
                ['status_code' => 404],
            ),
        ];

        yield [
            new PingUrlCheck(
                'https://example.com',
                expectedStatusCode: 404,
                httpClient: new MockHttpClient(new MockResponse(info: ['http_code' => 404, 'total_time' => 0.1]))
            ),
            Result::success('100ms'),
        ];

        yield [
            new PingUrlCheck(
                'https://example.com',
                expectedStatusCode: 200,
                httpClient: new MockHttpClient(new MockResponse(info: ['http_code' => 404]))
            ),
            Result::failure(
                '404: Not Found',
                'Expected status code 200, got 404',
                [
                    'expected_status_code' => 200,
                    'actual_status_code' => 404,
                ],
            ),
        ];

        yield [
            new PingUrlCheck(
                'https://example.com',
                warningDuration: 500,
                criticalDuration: 1000,
                httpClient: new MockHttpClient(new MockResponse(info: ['total_time' => 0.6]))
            ),
            Result::warning(
                'Response took 600ms',
                'Response took 600ms, which is above the warning threshold of 500ms',
                ['duration' => 0.6],
            ),
        ];

        yield [
            new PingUrlCheck(
                'https://example.com',
                warningDuration: 400,
                criticalDuration: 500,
                httpClient: new MockHttpClient(new MockResponse(info: ['total_time' => 0.6]))
            ),
            Result::failure(
                'Response took 600ms',
                'Response took 600ms, which is above the critical threshold of 500ms',
                ['duration' => 0.6],
            ),
        ];

        yield [
            new PingUrlCheck(
                'https://example.com',
                expectedContent: 'bar',
                httpClient: new MockHttpClient(new MockResponse('foo bar', info: ['total_time' => 0.6]))
            ),
            Result::success('600ms'),
        ];

        yield [
            new PingUrlCheck(
                'https://example.com',
                expectedContent: 'baz',
                httpClient: new MockHttpClient(new MockResponse('foo bar'))
            ),
            Result::failure(
                'Expected content not found',
                'Expected content "baz" not found in response body',
                [
                    'expected_content' => 'baz',
                    'actual_content' => 'foo bar',
                ],
            ),
        ];
    }
}
