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

use Liip\Monitor\Info\StorageInfo;
use Liip\Monitor\Utility\Percent;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class CacheInfo
{
    /** @var array{memory: StorageInfo, hits: int, misses: int} */
    private array $cache;

    final public function __construct()
    {
    }

    final public function memory(): StorageInfo
    {
        return $this->cache()['memory'];
    }

    final public function hits(): int
    {
        return $this->cache()['hits'];
    }

    final public function misses(): int
    {
        return $this->cache()['misses'];
    }

    final public function hitRate(): Percent
    {
        return Percent::calculate($this->hits(), $this->hits() + $this->misses(), divisionByZeroValue: 0)->constrain();
    }

    public function refresh(): static
    {
        unset($this->cache);

        return $this;
    }

    /**
     * @return array{usedMemory: int, freeMemory: int, hits: int, misses: int}
     */
    abstract protected function calculate(): array;

    /**
     * @return array{memory: StorageInfo, hits: int, misses: int}
     */
    private function cache(): array
    {
        if (isset($this->cache)) {
            return $this->cache;
        }

        $data = $this->calculate();

        return $this->cache = [
            'memory' => new StorageInfo($data['usedMemory'], $data['freeMemory'] + $data['usedMemory']),
            'hits' => $data['hits'],
            'misses' => $data['misses'],
        ];
    }
}
