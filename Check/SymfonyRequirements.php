<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SymfonyRequirements implements CheckInterface
{
    public function __construct($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $file));
        }

        require $file;
    }

    /**
     * {@inheritdoc}
     */
    public function check()
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

    public function getLabel()
    {
        return 'Symfony2 Requirements';
    }
}
