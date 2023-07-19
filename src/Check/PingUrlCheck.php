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

use Liip\Monitor\DependencyInjection\Configuration;
use Liip\Monitor\Result;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class PingUrlCheck implements ConfigurableCheck, \Stringable
{
    private HttpClientInterface $httpClient;

    /**
     * @param array<string,mixed> $options
     * @param ?float              $warningDuration  milliseconds
     * @param ?float              $criticalDuration milliseconds
     */
    public function __construct(
        private string $url,
        private string $method = 'GET',
        private array $options = [],
        private ?int $expectedStatusCode = null,
        private ?string $expectedContent = null,
        private ?float $warningDuration = null,
        private ?float $criticalDuration = null,
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function __toString(): string
    {
        return "Ping {$this->url}";
    }

    public function run(): Result
    {
        $response = $this->httpClient->request(\mb_strtoupper($this->method), $this->url, $this->options);
        $statusCode = $response->getStatusCode();
        $duration = $response->getInfo('total_time');

        if ($this->expectedContent && !\str_contains($content = $response->getContent(throw: false), $this->expectedContent)) {
            return Result::failure(
                'Expected content not found',
                \sprintf('Expected content "%s" not found in response body', $this->expectedContent),
                [
                    'expected_content' => $this->expectedContent,
                    'actual_content' => $content ?: '(none)',
                ],
            );
        }

        if ($result = $this->checkDuration($duration)) {
            return $result;
        }

        if ($result = $this->checkStatus($statusCode)) {
            return $result;
        }

        if (null !== $duration = $response->getInfo('total_time')) {
            $duration = \sprintf('%dms', \round($duration * 1000));
        }

        return Result::success($duration);
    }

    public static function configKey(): string
    {
        return 'ping_url';
    }

    public static function configInfo(): ?string
    {
        return null;
    }

    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition
    {
        return $node // @phpstan-ignore-line
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()
                    ->then(fn(string $url) => ['url' => $url])
                ->end()
                ->children()
                    ->scalarNode('url')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('method')->defaultValue('GET')->end()
                    ->variableNode('options')
                        ->info('See HttpClientInterface::DEFAULT_OPTIONS')
                        ->defaultValue([])
                    ->end()
                    ->integerNode('expected_status_code')
                        ->info('Leave null to ensure "successful" (2xx) status code')
                        ->defaultValue(null)
                    ->end()
                    ->scalarNode('expected_content')
                        ->defaultValue(null)
                    ->end()
                    ->integerNode('warning_duration')
                        ->info('Milliseconds')
                        ->defaultValue(null)
                    ->end()
                    ->integerNode('critical_duration')
                        ->info('Milliseconds')
                        ->defaultValue(null)
                    ->end()
                    ->append(Configuration::addSuiteConfig())
                    ->append(Configuration::addTtlConfig())
                    ->append(Configuration::addLabelConfig())
                    ->append(Configuration::addIdConfig())
                ->end()
            ->end()
        ;
    }

    public static function load(array $config, ContainerBuilder $container): void
    {
        foreach ($config as $key => $check) {
            $check['label'] = $check['label'] ?? \is_string($key) ? $key : null;

            $container->register(\sprintf('.liip_monitor.check.ping_url.%s', $key), self::class)
                ->setArguments([
                    $check['url'],
                    $check['method'],
                    $check['options'],
                    $check['expected_status_code'],
                    $check['expected_content'],
                    $check['warning_duration'],
                    $check['critical_duration'],
                ])
                ->addTag('liip_monitor.check', $check)
            ;
        }
    }

    private function checkStatus(int $code): ?Result
    {
        $summary = \sprintf('%d: %s', $code, Response::$statusTexts[$code] ?? 'Unknown');

        if ($this->expectedStatusCode && $code !== $this->expectedStatusCode) {
            return Result::failure(
                $summary,
                \sprintf('Expected status code %d, got %d', $this->expectedStatusCode, $code),
                [
                    'expected_status_code' => $this->expectedStatusCode,
                    'actual_status_code' => $code,
                ],
            );
        }

        if ($this->expectedStatusCode || ($code >= 200 && $code < 300)) {
            return null;
        }

        return Result::failure(
            $summary,
            \sprintf('Expected successful status code, got %d', $code),
            [
                'status_code' => $code,
            ],
        );
    }

    private function checkDuration(?float $duration): ?Result
    {
        if (null === $this->warningDuration && null === $this->criticalDuration) {
            return null;
        }

        if (null === $duration) {
            throw new \RuntimeException('Could not parse duration from response.');
        }

        $formattedDuration = \sprintf('%dms', \round($duration * 1000));

        if ($this->criticalDuration && $duration >= ($this->criticalDuration / 1000)) {
            return Result::failure(
                \sprintf('Response took %s', $formattedDuration),
                \sprintf('Response took %s, which is above the critical threshold of %dms', $formattedDuration, \round($this->criticalDuration)),
                [
                    'duration' => $duration,
                ],
            );
        }

        if ($this->warningDuration && $duration >= ($this->warningDuration / 1000)) {
            return Result::warning(
                \sprintf('Response took %s', $formattedDuration),
                \sprintf('Response took %s, which is above the warning threshold of %dms', $formattedDuration, \round($this->warningDuration)),
                [
                    'duration' => $duration,
                ],
            );
        }

        return null;
    }
}
