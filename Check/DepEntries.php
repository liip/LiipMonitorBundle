<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

/**
 * Checks all entries from deps are defined in deps.lock
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DepEntries implements CheckInterface
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

        return new Success();
    }

    public function getLabel()
    {
        return 'Deps files entries check';
    }
}
