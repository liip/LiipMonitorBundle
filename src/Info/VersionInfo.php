<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Info;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class VersionInfo implements \Stringable
{
    protected const DEFAULT_MAJOR = 0;
    protected const DEFAULT_MINOR = 0;
    protected const DEFAULT_PATCH = 0;

    protected int $major;
    protected int $minor;
    protected int $patch;

    final public function __construct(?string $version = null, private ?HttpClientInterface $httpClient = null)
    {
        if (null === $version) {
            $this->major = static::DEFAULT_MAJOR;
            $this->minor = static::DEFAULT_MINOR;
            $this->patch = static::DEFAULT_PATCH;

            return;
        }

        $parts = \explode('.', $version);

        $this->major = (int) $parts[0];
        $this->minor = (int) ($parts[1] ?? 0);
        $this->patch = (int) ($parts[2] ?? 0);
    }

    final public function __toString(): string
    {
        return $this->currentVersion();
    }

    final public function currentVersion(): string
    {
        return \sprintf('%d.%d.%d', $this->major, $this->minor, $this->patch);
    }

    final public function branch(): string
    {
        return \sprintf('%d.%d', $this->major, $this->minor);
    }

    final public function isMaintained(): bool
    {
        return !$this->isEol();
    }

    abstract public function isStable(): bool;

    abstract public function isSecurityOnly(): bool;

    abstract public function isEol(): bool;

    abstract public function releasedOn(): \DateTimeImmutable;

    final public function supportUntil(): \DateTimeImmutable
    {
        return $this->securitySupportUntil();
    }

    abstract public function activeSupportUntil(): \DateTimeImmutable;

    abstract public function securitySupportUntil(): \DateTimeImmutable;

    abstract public function latestPatchVersion(): string;

    abstract public function latestPatchReleased(): \DateTimeImmutable;

    final public function isPatchUpdateRequired(): bool
    {
        return \version_compare($this->currentVersion(), $this->latestPatchVersion(), '<');
    }

    final public function isMinorUpdateRequired(): bool
    {
        return null !== $this->nextMinorVersion();
    }

    final public function isMajorUpdateRequired(): bool
    {
        return null !== $this->nextMajorVersion();
    }

    abstract public function nextMinorVersion(): ?static;

    abstract public function nextMajorVersion(): ?static;

    final protected function httpClient(): HttpClientInterface
    {
        if ($this->httpClient) {
            return $this->httpClient;
        }

        if (!\interface_exists(HttpClientInterface::class)) {
            throw new \RuntimeException('symfony/http-client is required. Try running "composer require symfony/http-client".');
        }

        return $this->httpClient = HttpClient::create();
    }
}
