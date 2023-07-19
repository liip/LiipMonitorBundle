<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Info\Symfony;

use Liip\Monitor\Info\VersionInfo;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SymfonyVersionInfo extends VersionInfo
{
    protected const DEFAULT_MAJOR = Kernel::MAJOR_VERSION;
    protected const DEFAULT_MINOR = Kernel::MINOR_VERSION;
    protected const DEFAULT_PATCH = Kernel::RELEASE_VERSION;

    /** @var array<string,scalar> */
    private array $release;

    public function __clone(): void
    {
        unset($this->release);
    }

    public function isStable(): bool
    {
        return !$this->release()['is_eomed'];
    }

    public function isSecurityOnly(): bool
    {
        return !$this->isEol() && $this->release()['is_eomed'];
    }

    public function isEol(): bool
    {
        return (bool) $this->release()['is_eoled'];
    }

    public function releasedOn(): \DateTimeImmutable
    {
        return self::parseMonth((string) $this->release()['release_date']);
    }

    public function activeSupportUntil(): \DateTimeImmutable
    {
        if ($this->usingInstalled()) {
            return self::parseMonth(Kernel::END_OF_MAINTENANCE);
        }

        return self::parseMonth((string) $this->release()['eom']);
    }

    public function securitySupportUntil(): \DateTimeImmutable
    {
        if ($this->usingInstalled()) {
            return self::parseMonth(Kernel::END_OF_LIFE);
        }

        return self::parseMonth((string) $this->release()['eol']);
    }

    public function latestPatchVersion(): string
    {
        return (string) $this->release()['latest_patch_version'];
    }

    public function latestPatchReleased(): \DateTimeImmutable
    {
        // todo use github/packagist api?
        throw new \BadMethodCallException('Not implemented yet');
    }

    public function nextMinorVersion(): ?static
    {
        if (4 === $this->minor) {
            return null;
        }

        $clone = clone $this;
        ++$clone->minor;
        $clone->patch = 0;

        return $clone->isReleased() ? $clone : null;
    }

    public function nextMajorVersion(): ?static
    {
        $clone = clone $this;
        ++$clone->major;
        $clone->minor = 0;
        $clone->patch = 0;

        return $clone->isReleased() ? $clone : null;
    }

    public function isLts(): bool
    {
        return (bool) $this->release()['is_lts'];
    }

    public function isReleased(): bool
    {
        return (bool) $this->release()['is_released'];
    }

    public function isLatest(): bool
    {
        return (bool) $this->release()['is_latest'];
    }

    public function refresh(): self
    {
        unset($this->release);

        return $this;
    }

    private static function parseMonth(string $value): \DateTimeImmutable
    {
        return new \DateTimeImmutable(\sprintf('%s-%s-01', ...\array_reverse(\explode('/', $value, 2))));
    }

    /**
     * @return array<string,scalar>
     */
    private function release(): array
    {
        if (isset($this->release)) {
            return $this->release;
        }

        $this->release = $this->httpClient()->request('GET', \sprintf('https://symfony.com/releases/%s.json', $this->branch()))->toArray();

        if (isset($this->release['error_message'])) {
            throw new \RuntimeException($this->release['error_message']);
        }

        return $this->release;
    }

    private function usingInstalled(): bool
    {
        return self::DEFAULT_MAJOR === $this->major && self::DEFAULT_MINOR === $this->minor && self::DEFAULT_PATCH === $this->patch;
    }
}
