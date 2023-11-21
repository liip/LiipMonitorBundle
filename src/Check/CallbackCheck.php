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

use Liip\Monitor\Check;
use Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallbackCheck implements Check, \Stringable
{
    /** @var \Closure():(void|Result|bool|null) */
    private \Closure $callback;

    /**
     * @param callable():(void|Result|bool|null) $callback
     */
    public function __construct(private string $label, callable $callback)
    {
        $this->callback = $callback(...);
    }

    public function __toString(): string
    {
        return $this->label;
    }

    public function run(): Result
    {
        $result = ($this->callback)();

        return match (true) {
            $result instanceof Result => $result,
            true === $result, null === $result => Result::success(),
            false === $result => Result::failure('Fail'),
            default => Result::failure('Unrecognized result returned from callback.'),
        };
    }
}
