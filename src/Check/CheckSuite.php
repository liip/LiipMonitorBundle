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

use Liip\Monitor\Event\PostRunCheckSuiteEvent;
use Liip\Monitor\Event\PreRunCheckSuiteEvent;
use Liip\Monitor\Result\ResultSet;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CheckSuite implements \Countable, \Stringable
{
    /**
     * @internal
     *
     * @param array<string,CheckRunner> $checks
     */
    public function __construct(private ?string $name, private array $checks, private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function __toString(): string
    {
        return $this->name ?? 'all';
    }

    public function run(bool $cache = true): ResultSet
    {
        $this->eventDispatcher->dispatch(new PreRunCheckSuiteEvent($this));

        $results = [];

        foreach ($this->checks as $check) {
            $results[] = $check->run($cache);
        }

        $results = new ResultSet($results);

        $this->eventDispatcher->dispatch(new PostRunCheckSuiteEvent($this, $results));

        return $results;
    }

    public function count(): int
    {
        return \count($this->checks);
    }

    /**
     * @return array<string,CheckRunner>
     */
    public function checks(): array
    {
        return $this->checks;
    }
}
