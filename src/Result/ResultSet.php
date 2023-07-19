<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 *
 * @implements \IteratorAggregate<string,ResultContext>
 */
final class ResultSet implements \IteratorAggregate, \Countable, \JsonSerializable
{
    /**
     * @param ResultContext[] $results
     */
    public function __construct(private array $results)
    {
    }

    public function failures(): self
    {
        return $this->ofStatus(Status::FAILURE);
    }

    public function errors(): self
    {
        return $this->ofStatus(Status::ERROR);
    }

    public function defects(Status ...$status): self
    {
        return $this->ofStatus(...\array_merge([Status::FAILURE, Status::ERROR], $status));
    }

    public function warnings(): self
    {
        return $this->ofStatus(Status::WARNING);
    }

    public function successes(): self
    {
        return $this->ofStatus(Status::SUCCESS);
    }

    public function skipped(): self
    {
        return $this->ofStatus(Status::SKIP);
    }

    public function unknowns(): self
    {
        return $this->ofStatus(Status::UNKNOWN);
    }

    public function ofStatus(Status ...$status): self
    {
        return new self(
            \array_filter(
                $this->results,
                static fn(ResultContext $result) => \in_array($result->status(), $status, true),
            )
        );
    }

    public function notOfStatus(Status ...$status): self
    {
        return new self(
            \array_filter(
                $this->results,
                static fn(ResultContext $result) => !\in_array($result->status(), $status, true),
            )
        );
    }

    public function duration(): float
    {
        return \array_sum(\array_map(static fn(ResultContext $result) => $result->duration(), $this->results));
    }

    /**
     * @return array<string,ResultContext>
     */
    public function all(): array
    {
        return $this->results;
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->results as $result) {
            yield $result->check()->id() => $result;
        }
    }

    public function count(): int
    {
        return \count($this->results);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'results' => $this->results,
            'duration' => $this->duration(),
        ];
    }
}
