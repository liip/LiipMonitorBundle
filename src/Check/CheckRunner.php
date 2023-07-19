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

use Liip\Monitor\AsCheck;
use Liip\Monitor\Check;
use Liip\Monitor\Event\PostRunCheckEvent;
use Liip\Monitor\Event\PreRunCheckEvent;
use Liip\Monitor\Result;
use Liip\Monitor\Result\ResultContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CheckRunner extends CheckContext
{
    /**
     * @internal
     */
    public function __construct(
        private CacheInterface $cache,
        private EventDispatcherInterface $eventDispatcher,
        Check $check,
        ?int $ttl = null,
        string|array $suite = [],
        ?string $label = null,
        ?string $id = null,
    ) {
        parent::__construct($check, $ttl, $suite, $label, $id);
    }

    public function run(bool $cache = true): ResultContext
    {
        if ($cache && AsCheck::DISABLE_CACHE === $this->ttl()) {
            $cache = false;
        }

        $ttl = $cache ? $this->ttl() : null;

        /** @var \Closure():array{0:Result,1:float} $runner */
        $runner = function(): array {
            $start = \microtime(true);

            try {
                $result = parent::run();
            } catch (\Throwable $e) {
                $result = Result::error($e);
            }

            return [$result, \microtime(true) - $start];
        };

        if ($ttl) {
            $runner = function() use ($ttl, $runner) {
                return $this->cache->get(
                    'liip-monitor-result-'.$this->id(),
                    function(ItemInterface $item) use ($ttl, $runner) {
                        $item->expiresAfter($ttl);

                        if ($this->cache instanceof TagAwareCacheInterface) {
                            $item->tag('liip-monitor-result');
                        }

                        return $runner();
                    },
                );
            };
        }

        $this->eventDispatcher->dispatch(new PreRunCheckEvent($this));

        [$result, $duration] = $runner();
        $context = new ResultContext($this, $result, $duration);

        $this->eventDispatcher->dispatch(new PostRunCheckEvent($context));

        return $context;
    }
}
