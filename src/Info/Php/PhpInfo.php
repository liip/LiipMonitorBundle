<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Info\Php;

use Liip\Monitor\Info\Symfony\SymfonyVersionInfo;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PhpInfo implements \Stringable
{
    public function __construct(private ?HttpClientInterface $httpClient = null)
    {
    }

    public function __toString(): string
    {
        \ob_start();
        \phpinfo();

        return \ob_get_clean() ?: throw new \RuntimeException('Unable to retrieve phpinfo().');
    }

    public function opcache(): OpCacheInfo
    {
        return new OpCacheInfo();
    }

    public function apcu(): ApcuCacheInfo
    {
        return new ApcuCacheInfo();
    }

    public function version(): PhpVersionInfo
    {
        return new PhpVersionInfo(httpClient: $this->httpClient);
    }

    public function symfonyVersion(): SymfonyVersionInfo
    {
        return new SymfonyVersionInfo(httpClient: $this->httpClient);
    }
}
