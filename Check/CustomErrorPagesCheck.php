<?php

namespace Liip\MonitorBundle\Check;

use Liip\Monitor\Check\Check;
use Liip\Monitor\Result\CheckResult;
use Liip\Monitor\Exception\CheckFailedException;

use Symfony\Bundle\TwigBundle\DependencyInjection\Configuration;

/**
 * Checks if error pages have been customized.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class CustomErrorPagesCheck extends Check
{
    /**
     * @var string
     */
    protected $kernelRootDir;

    /**
     * @var array
     */
    protected $errorCodes;

    /**
     * @var string
     */
    protected $exceptionController;

    /**
     * Construct.
     *
     * @param array $errorCodes
     * @param string $kernelRootDir
     * @param string $exceptionController
     */
    public function __construct($errorCodes, $kernelRootDir, $exceptionController)
    {
        $this->errorCodes = $errorCodes;
        $this->kernelRootDir = $kernelRootDir;
        $this->exceptionController = $exceptionController;
    }

    /**
     * @see Liip\Monitor\Check\CheckInterface::check()
     */
    public function check()
    {
        try {
            // check if twig exception controller is not the default one.
            $config = new Configuration();
            $tree =  $config->getConfigTreeBuilder()->buildTree();

            $reflectionTree = new \ReflectionClass($tree);
            $reflectionChildren = $reflectionTree->getProperty('children');
            $reflectionChildren->setAccessible(true);

            $values = $reflectionChildren->getValue($tree);

            // we suppose pages has been customized if the execption controller is not the default one,
            // so we don't look for template file in this case.
            if ($values['exception_controller']->getDefaultValue() == $this->exceptionController) {
                $missingTemplate = array();

                foreach ($this->errorCodes as $errorCode) {
                    $template = sprintf('%s/Resources/TwigBundle/views/Exception/error%d.html.twig', $this->kernelRootDir, $errorCode);

                    if (!file_exists($template)) {
                        $missingTemplate[] = $errorCode;
                    }
                }

                if (count($missingTemplate) > 0) {
                    throw new CheckFailedException(sprintf('No custom error page found for the following codes: %s', implode(', ', $missingTemplate)));
                }
            }

            $result = $this->buildResult('OK', CheckResult::OK);

        } catch (\Exception $e) {
            $result = $this->buildResult($e->getMessage(), CheckResult::CRITICAL);
        }

        return $result;
    }

    /**
     * @see Liip\Monitor\Check\Check::getName()
     */
    public function getName()
    {
        return 'Custom error pages';
    }
}
