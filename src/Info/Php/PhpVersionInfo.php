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

use Liip\Monitor\Info\VersionInfo;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PhpVersionInfo extends VersionInfo
{
    protected const DEFAULT_MAJOR = \PHP_MAJOR_VERSION;
    protected const DEFAULT_MINOR = \PHP_MINOR_VERSION;
    protected const DEFAULT_PATCH = \PHP_RELEASE_VERSION;

    /** @var mixed[] */
    private array $states;

    /** @var mixed[] */
    private array $active;

    public function isStable(): bool
    {
        return 'stable' === $this->currentState();
    }

    public function isSecurityOnly(): bool
    {
        return 'security' === $this->currentState();
    }

    public function isEol(): bool
    {
        return 'eol' === $this->currentState();
    }

    public function releasedOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->stateVersion()['initial_release']);
    }

    public function activeSupportUntil(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->stateVersion()['active_support_end']);
    }

    public function securitySupportUntil(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->stateVersion()['security_support_end']);
    }

    public function latestPatchVersion(): string
    {
        return $this->activeVersion()['version'] ?? throw new \RuntimeException('Unable to determine latest PHP version.');
    }

    public function latestPatchReleased(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->activeVersion()['date']);
    }

    public function nextMinorVersion(): ?static
    {
        if (!isset($this->states()[(string) $this->major][\sprintf('%d.%d', $this->major, $this->minor + 1)])) {
            return null;
        }

        $clone = clone $this;
        ++$clone->minor;
        $clone->patch = 0;

        return $clone;
    }

    public function nextMajorVersion(): ?static
    {
        if (!isset($this->states()[(string) ($this->major + 1)])) {
            return null;
        }

        $clone = clone $this;
        ++$clone->major;
        $clone->minor = 0;
        $clone->patch = 0;

        return $clone;
    }

    public function refresh(): self
    {
        unset($this->states, $this->active);

        return $this;
    }

    private function currentState(): string
    {
        return $this->stateVersion()['state'] ?? 'eol';
    }

    /**
     * @return array<string,string>
     */
    private function stateVersion(): array
    {
        return $this->states()[(string) $this->major][$this->branch()] ?? throw new \RuntimeException('Unable to determine PHP version status.');
    }

    /**
     * @return array<string,string>
     */
    private function activeVersion(): array
    {
        return $this->active()[(string) $this->major][$this->branch()] ?? throw new \RuntimeException('Unable to determine PHP version status.');
    }

    /**
     * @return mixed[]
     */
    private function states(): array
    {
        return $this->states ??= $this->httpClient()->request('GET', 'https://www.php.net/releases/states')->toArray();
    }

    /**
     * @return mixed[]
     */
    private function active(): array
    {
        return $this->active ??= $this->httpClient()->request('GET', 'https://www.php.net/releases/active')->toArray();
    }
}
