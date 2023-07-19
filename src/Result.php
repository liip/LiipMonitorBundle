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

use Liip\Monitor\Result\Status;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Result implements \Stringable
{
    /**
     * @internal
     *
     * @param array<string,mixed> $context
     */
    protected function __construct(
        private Status $status,
        private string $summary,
        private ?string $detail,
        private array $context,
    ) {
    }

    final public function __toString(): string
    {
        if (Status::ERROR === $this->status) {
            return \sprintf('%s: %s', $this->summary, $this->detail);
        }

        return $this->summary;
    }

    /**
     * @param array<string,mixed> $context
     */
    final public static function success(?string $summary = null, ?string $detail = null, array $context = []): self
    {
        return new self(Status::SUCCESS, $summary ?? 'Success', $detail, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    final public static function warning(string $summary, ?string $detail = null, array $context = []): self
    {
        return new self(Status::WARNING, $summary, $detail, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    final public static function failure(string $summary, ?string $detail = null, array $context = []): self
    {
        return new self(Status::FAILURE, $summary, $detail, $context);
    }

    final public static function error(\Throwable $exception): self
    {
        return new self(
            Status::ERROR,
            $exception::class,
            \sprintf('%s: %s', $exception::class, $exception->getMessage()),
            \array_filter([
                'exception' => $exception,
                'message' => $exception->getMessage(),
                'stack_trace' => $exception->getTraceAsString(),
                'previous' => $exception->getPrevious(),
                'previous_message' => $exception->getPrevious()?->getMessage(),
                'previous_stack_trace' => $exception->getPrevious()?->getTraceAsString(),
            ]),
        );
    }

    /**
     * @param array<string,mixed> $context
     */
    final public static function unknown(string $summary, ?string $detail = null, array $context = []): self
    {
        return new self(Status::UNKNOWN, $summary, $detail, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    final public static function skip(string $summary, ?string $detail = null, array $context = []): self
    {
        return new self(Status::SKIP, $summary, $detail, $context);
    }

    final public function status(): Status
    {
        return $this->status;
    }

    final public function summary(): string
    {
        return $this->summary;
    }

    /**
     * @return array<string,mixed>
     */
    final public function context(): array
    {
        return $this->context;
    }

    /**
     * @return array<string,scalar>
     */
    final public function normalizedContext(): array
    {
        return \array_map(
            function(mixed $v) {
                if (\is_scalar($v)) {
                    return $v;
                }

                if ($v instanceof \DateTimeInterface) {
                    return $v->format(\DateTimeInterface::ATOM);
                }

                return \get_debug_type($v);
            },
            $this->context,
        );
    }

    final public function detail(): ?string
    {
        return $this->detail;
    }
}
