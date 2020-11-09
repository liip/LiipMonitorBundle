<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SymfonyRequirements implements CheckInterface
{
    /**
     * @var string|null
     */
    private $label;

    public function __construct(string $file, string $label = null)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $file));
        }

        $this->label = $label;

        require $file;
    }

    public function check(): ResultInterface
    {
        $symfonyRequirements = new \SymfonyRequirements();

        if (count($symfonyRequirements->getFailedRequirements())) {
            return new Failure('Some Symfony2 requirements are not met.');
        }

        if (count($symfonyRequirements->getFailedRecommendations())) {
            return new Warning('Some Symfony2 recommendations are not met.');
        }

        return new Success();
    }

    public function getLabel(): string
    {
        return $this->label ?? 'Symfony2 Requirements';
    }
}
