<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor;

use Liip\Monitor\Info\Php\PhpInfo;
use Liip\Monitor\Info\StorageInfo;
use Liip\Monitor\System\Webserver;
use Liip\Monitor\Utility\Percent;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class System implements \Stringable
{
    public function __construct(private ?HttpClientInterface $httpClient = null)
    {
    }

    public function __toString(): string
    {
        return \PHP_OS;
    }

    public function php(): PhpInfo
    {
        return new PhpInfo($this->httpClient);
    }

    public function webserver(): Webserver
    {
        return new Webserver();
    }

    public function isRebootRequired(): bool
    {
        throw new \BadMethodCallException(\sprintf('The "%s::%s()" method is not supported on this system.', static::class, __FUNCTION__));
    }

    /**
     * Returns 1, 5, and 15 minute system load averages.
     *
     * @return array{0: Percent, 1: Percent, 2: Percent}
     */
    public function loadAverages(): array
    {
        return \array_map( // @phpstan-ignore-line
            static fn(float $load) => Percent::fromDecimal($load)->constrain(),
            \sys_getloadavg() ?: throw new \RuntimeException('Unable to retrieve average system loads.')
        );
    }

    public function disk(string $path = '/'): StorageInfo
    {
        if (false === $total = @\disk_total_space($path)) {
            throw new \RuntimeException('Unable to retrieve total disk space.');
        }

        if (false === $free = @\disk_free_space($path)) {
            throw new \RuntimeException('Unable to retrieve free disk space.');
        }

        return new StorageInfo(
            used: (int) ($total - $free),
            total: (int) $total,
        );
    }

    public function memory(): StorageInfo
    {
        throw new \BadMethodCallException(\sprintf('The "%s::%s()" method is not supported on this system.', static::class, __FUNCTION__));
    }
}
