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

use Liip\Monitor\Check\CheckContext;
use Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ResultContext extends Result implements \JsonSerializable
{
    /**
     * @internal
     */
    public function __construct(private CheckContext $checkContext, Result $result, private float $duration)
    {
        parent::__construct($result->status(), $result->summary(), $result->detail(), $result->context());
    }

    public function check(): CheckContext
    {
        return $this->checkContext;
    }

    /**
     * @return float in seconds
     */
    public function duration(): float
    {
        return $this->duration;
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'check' => $this->check(),
            'status' => $this->status(),
            'summary' => $this->summary(),
            'detail' => $this->detail(),
            'context' => $this->normalizedContext(),
            'duration' => $this->duration(),
        ];
    }
}
