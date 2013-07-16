<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\HttpKernel\Kernel;
use ZendDiagnostics\Check\AbstractCheck;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

/**
 * Checks all entries from deps are defined in deps.lock
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DepsEntries extends AbstractCheck
{
    /**
     * @var string
     */
    protected $kernelRootDir;

    /**
     * Construct.
     *
     * @param string $kernelRootDir
     */
    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * @see Liip\Monitor\Check\CheckInterface::check()
     */
    public function check()
    {
        $deps = parse_ini_file($this->kernelRootDir.'/../deps', true, INI_SCANNER_RAW);
        $depsLock = array();
        foreach (file($this->kernelRootDir.'/../deps.lock', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $values = explode(' ', $line);
            $depsLock[$values[0]] = isset($values[1]) ? $values[1] : '';
        }

        $unlocked = array();
        foreach ($deps as $name => $data) {
            if (!isset($depsLock[$name]) || $depsLock[$name] == '') {
                $unlocked[] = $name;
            }
        }

        if (count($unlocked) > 0) {
            return new Failure(sprintf('The following entries are not defined in the deps.lock file: %s', implode(', ', $unlocked)));
        }

        $currentVersion = Kernel::VERSION;
        if (version_compare($currentVersion, '2.1.0-dev') >= 0) {
            return new Warning("Using 'deps.lock' file with Symfony 2.1+ is discouraged, use http://getcomposer.org instead.");
        }

        return new Success();
    }

    /**
     * @see Liip\Monitor\Check\Check::getName()
     */
    public function getName()
    {
        return 'Deps files entries check';
    }
}
