<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\System;

use Liip\Monitor\Info\StorageInfo;
use Liip\Monitor\System;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LinuxSystem extends System
{
    /** @var array<string,int> */
    private array $memoryInfo;

    /** @var array<string,string> */
    private array $osInfo;

    public function __toString(): string
    {
        return $this->osInfo()['PRETTY_NAME'] ?? parent::__toString();
    }

    public function isRebootRequired(): bool
    {
        return \file_exists('/var/run/reboot-required');
    }

    public function memory(): StorageInfo
    {
        return new StorageInfo(
            $this->memoryInfo()['MemTotal'] - $this->memoryInfo()['MemFree'] - ($this->memoryInfo()['Buffers'] ?? 0) - ($this->memoryInfo()['Cached'] ?? 0),
            $this->memoryInfo()['MemTotal']
        );
    }

    public function cachedMemory(): StorageInfo
    {
        if (!isset($this->memoryInfo()['Cached'])) {
            throw new \RuntimeException('Unable to retrieve cached memory info.');
        }

        return new StorageInfo($this->memoryInfo()['Cached'], $this->memoryInfo()['MemTotal']);
    }

    public function bufferedMemory(): StorageInfo
    {
        if (!isset($this->memoryInfo()['Buffers'])) {
            throw new \RuntimeException('Unable to retrieve buffered memory info.');
        }

        return new StorageInfo($this->memoryInfo()['Buffers'], $this->memoryInfo()['MemTotal']);
    }

    public function swapMemory(): StorageInfo
    {
        if (!isset($this->memoryInfo()['SwapTotal'], $this->memoryInfo()['SwapFree'])) {
            throw new \RuntimeException('Unable to retrieve swap memory info.');
        }

        return new StorageInfo(
            $this->memoryInfo()['SwapTotal'] - $this->memoryInfo()['SwapFree'],
            $this->memoryInfo()['SwapTotal']
        );
    }

    public function refresh(): self
    {
        unset($this->memoryInfo);

        return $this;
    }

    /**
     * @return array<string,int>
     */
    private function memoryInfo(): array
    {
        if (isset($this->memoryInfo)) {
            return $this->memoryInfo;
        }

        if (!\file_exists($filename = '/proc/meminfo')) {
            throw new \RuntimeException('Unable to retrieve memory info.');
        }

        $file = \trim(@\file_get_contents($filename) ?: throw new \RuntimeException('Unable to retrieve memory info.'));

        /*
         * Output looks like:
         *
         * MemTotal:       12287084 kB
         * MemFree:          356084 kB
         * MemAvailable:    1197084 kB
         * ...
         */
        $info = [];

        foreach (\explode("\n", $file) as $line) {
            if (!\preg_match('#(\w+):\s+(\d+)#', $line, $matches)) {
                continue;
            }

            $info[$matches[1]] = (int) $matches[2] * 1000;
        }

        if (!isset($info['MemTotal'], $info['MemFree'])) {
            throw new \RuntimeException('Unable to retrieve memory info.');
        }

        return $this->memoryInfo = $info;
    }

    /**
     * @return array<string,string>
     */
    private function osInfo(): array
    {
        if (isset($this->osInfo)) {
            return $this->osInfo;
        }

        if (false === $files = \glob('/etc/*-release')) {
            return [];
        }

        $osInfo = [];

        foreach ($files as $file) {
            $osInfo[] = \parse_ini_file($file) ?: [];
        }

        return $this->osInfo = \array_merge(...$osInfo);
    }
}
