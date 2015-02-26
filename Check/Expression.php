<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Expression implements CheckInterface
{
    private $label;
    private $warningCheck;
    private $criticalCheck;
    private $warningMessage;
    private $criticalMessage;

    public function __construct($label, $warningCheck = null, $criticalCheck = null, $warningMessage = null, $criticalMessage = null)
    {
        if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
            throw new \Exception('The symfony/expression-language is required for this check.');
        }

        if (!$warningCheck && !$criticalCheck) {
            throw new \InvalidArgumentException('Not checks set.');
        }

        $this->label = $label;
        $this->warningCheck = $warningCheck;
        $this->warningMessage = $warningMessage;
        $this->criticalCheck = $criticalCheck;
        $this->criticalMessage = $criticalMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function check()
    {
        $language = $this->getExpressionLanguage();

        if ($this->criticalCheck && false === $language->evaluate($this->criticalCheck)) {
            return new Failure($this->criticalMessage);
        }

        if ($this->warningCheck && false === $language->evaluate($this->warningCheck)) {
            return new Warning($this->warningMessage);
        }

        return new Success();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    protected function getExpressionLanguage()
    {
        $language = new ExpressionLanguage();
        $language->register(
            'ini',
            function ($value) {
                return $value;
            },
            function ($arguments, $value) {
                return ini_get($value);
            }
        );

        return $language;
    }
}
