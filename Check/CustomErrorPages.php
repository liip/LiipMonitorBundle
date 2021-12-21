<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

/**
 * Checks if error pages have been customized.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class CustomErrorPages implements CheckInterface
{
    /**
     * @var array
     */
    protected $errorCodes;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $projectDir;

    public function __construct(array $errorCodes, $path, $projectDir)
    {
        $this->errorCodes = $errorCodes;
        $this->path = $path;
        $this->projectDir = $projectDir;
    }

    /**
     * @return ResultInterface
     */
    public function check()
    {
        $dir = $this->getCustomTemplateDirectory();
        $missingTemplates = [];

        foreach ($this->errorCodes as $errorCode) {
            $template = sprintf('%s/error%d.html.twig', $dir, $errorCode);

            if (!file_exists($template)) {
                $missingTemplates[] = $errorCode;
            }
        }

        if (count($missingTemplates) > 0) {
            return new Failure(sprintf('No custom error page found for the following codes: %s', implode(', ', $missingTemplates)));
        }

        return new Success();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Custom error pages';
    }

    private function getCustomTemplateDirectory(): string
    {
        if ($this->projectDir !== $this->path) {
            return $this->path; // using custom directory
        }

        if (file_exists($dir = $this->projectDir.'/templates/bundles/TwigBundle/Exception')) {
            return $dir; // using standard 4.0+ directory
        }

        return $this->projectDir.'/app/Resources/TwigBundle/views/Exception'; // assume using 3.4 dir structure
    }
}
