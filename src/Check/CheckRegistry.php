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
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CheckRegistry
{
    /** @var array<string,CheckRunner> */
    private array $normalized;

    /**
     * @internal
     *
     * @param Check[]|CheckInterface[] $checks
     */
    public function __construct(
        private iterable $checks,
        private CacheInterface $cache,
        private EventDispatcherInterface $eventDispatcher,
        private ?int $defaultTtl,
    ) {
    }

    public function get(string $id): CheckRunner
    {
        return $this->normalized()[$id] ?? throw new \InvalidArgumentException(\sprintf('Check with id "%s" does not exist.', $id));
    }

    public function suite(?string $name = null): CheckSuite
    {
        return new CheckSuite(
            $name,
            !$name ? $this->normalized() : \array_filter(
                $this->normalized(),
                static fn(CheckRunner $check) => \in_array($name, $check->suites(), true),
            ),
            $this->eventDispatcher,
        );
    }

    /**
     * @return array<string,CheckRunner>
     */
    private function normalized(): array
    {
        if (isset($this->normalized)) {
            return $this->normalized;
        }

        $this->normalized = [];

        foreach ($this->checks as $check) {
            $context = CheckContext::wrap($check)->createRunner($this->cache, $this->eventDispatcher, $this->defaultTtl);

            if (isset($this->normalized[$context->id()])) {
                $duplicate = $this->normalized[$context->id()];

                throw new \LogicException(\sprintf('Check "%s" (%s) has an id (%s) that is duplicated with check "%s" (%s)', $context->label(), $context->wrapped()::class, $context->id(), $duplicate->label(), $duplicate->wrapped()::class));
            }

            $this->normalized[$context->id()] = $context;
        }

        return $this->normalized;
    }
}
