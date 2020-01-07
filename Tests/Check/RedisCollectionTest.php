<?php

namespace Liip\MonitorBundle\Tests\Check;

use Laminas\Diagnostics\Check\Redis;
use Liip\MonitorBundle\Check\RedisCollection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

final class RedisCollectionTest extends TestCase
{
    const AUTH = 'my-super-secret-password';

    /**
     * @test
     * @dataProvider provideDsnWithAut
     */
    public function handleDsnWithAuth(string $dsn)
    {
        $config = [
          'dsn' => $dsn,
          'host' => 'localhost',
          'port' => 6379,
          'password' => null,
        ];

        $collection = new RedisCollection(['default' => $config]);
        $checks = $collection->getChecks();

        /** @var Redis $check */
        $check = $checks['redis_default'];

        $this->assertAuthPropertyValue($check, self::AUTH);
    }

    private function assertAuthPropertyValue(Redis $check, string $auth)
    {
        try {
            $refClass = new ReflectionClass($check);
            $authProp = $refClass->getProperty('auth');
            $authProp->setAccessible(true);
            self::assertSame($auth, $authProp->getValue($check));
        } catch (ReflectionException $e) {
            self::fail($e->getMessage());
        }
    }

    public function provideDsnWithAut(): array
    {
        return [
          'incompatible with parse_url' => [sprintf('redis://%s@127.0.0.1:6379', static::AUTH)],
          'compatible with parse_url' => [sprintf('redis://irrelevant-user:%s@127.0.0.1:6379', static::AUTH)],
        ];
    }
}
