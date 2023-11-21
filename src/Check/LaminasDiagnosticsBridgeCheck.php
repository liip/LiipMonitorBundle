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

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\SkipInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\WarningInterface;
use Liip\Monitor\Check;
use Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LaminasDiagnosticsBridgeCheck implements Check, \Stringable
{
    public function __construct(
        private readonly CheckInterface $check,
        private readonly ?string $label = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->label ?? $this->check->getLabel();
    }

    public function run(): Result
    {
        $result = $this->check->check();

        if (!$result instanceof ResultInterface) {
            throw new \RuntimeException(\sprintf('Laminas check result is not an instance of %s.', ResultInterface::class));
        }

        $context = $result->getData();

        if (!\is_array($context) || \array_is_list($context)) {
            $context = ['data' => $context];
        }

        return match (true) {
            $result instanceof SuccessInterface => Result::success($result->getMessage() ?: 'Success', context: $context),
            $result instanceof WarningInterface => Result::warning($result->getMessage() ?: 'Warning', context: $context),
            $result instanceof FailureInterface => Result::failure($result->getMessage() ?: 'Failure', context: $context),
            $result instanceof SkipInterface => Result::skip($result->getMessage() ?: 'Skipped', context: $context),
            default => Result::unknown($result->getMessage(), context: $context),
        };
    }
}
