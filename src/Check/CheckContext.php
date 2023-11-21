<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Liip\Monitor\Check;
use Liip\Monitor\Result;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\CacheInterface;

use function Symfony\Component\String\s;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class CheckContext implements Check, \JsonSerializable, \Stringable
{
    private Check $check;

    /** @var string[] */
    private array $suites;

    /**
     * @internal
     *
     * @param string|string[] $suite
     */
    public function __construct(
        Check|CheckInterface $check,
        private ?int $ttl = null,
        string|array $suite = [],
        private ?string $label = null,
        private ?string $id = null,
    ) {
        if ($check instanceof self) {
            throw new \InvalidArgumentException('Cannot wrap a CheckContext in another CheckContext.');
        }

        $this->check = !$check instanceof Check ? new LaminasDiagnosticsBridgeCheck($check) : $check;
        $this->suites = (array) $suite;
    }

    final public function __toString(): string
    {
        return $this->label();
    }

    final public function label(): string
    {
        if (isset($this->label)) {
            return $this->label;
        }

        if ($this->check instanceof \Stringable) {
            return $this->label = $this->check;
        }

        return $this->label = s((new \ReflectionClass($this->check))->getShortName())
            ->snake()
            ->trimSuffix('_check')
            ->replace('_', ' ')
            ->title(allWords: true)
            ->toString()
        ;
    }

    /**
     * @internal
     */
    final public static function wrap(Check|CheckInterface $check): self
    {
        return $check instanceof self ? $check : new self($check);
    }

    public function run(): Result
    {
        return $this->check->run();
    }

    final public function id(): string
    {
        return $this->id ??= \hash('crc32c', $this->check::class.$this->label());
    }

    final public function wrapped(): Check
    {
        return $this->check;
    }

    final public function ttl(): ?int
    {
        return $this->ttl;
    }

    /**
     * @return string[]
     */
    final public function suites(): array
    {
        return $this->suites;
    }

    /**
     * @return array<string,string>
     */
    final public function jsonSerialize(): array
    {
        return [
            'label' => $this->label(),
            'id' => $this->id(),
        ];
    }

    /**
     * @internal
     */
    final public function createRunner(
        CacheInterface $cache,
        EventDispatcherInterface $eventDispatcher,
        ?int $defaultTtl,
    ): CheckRunner {
        if ($this instanceof CheckRunner) {
            return $this;
        }

        return new CheckRunner(
            cache: $cache,
            eventDispatcher: $eventDispatcher,
            check: $this->check,
            ttl: $this->ttl ?? $defaultTtl,
            suite: $this->suites,
            label: $this->label,
            id: $this->id,
        );
    }
}
