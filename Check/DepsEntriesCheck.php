<?php

namespace Liip\Monitor\Check;

use Liip\Monitor\Check\Check;
use Liip\Monitor\Exception\CheckFailedException;
use Liip\Monitor\Result\CheckResult;

/**
 * Checks all entries from deps are defined in deps.lock
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DepsEntriesCheck extends Check
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
        try {
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
                throw new CheckFailedException(sprintf('The following entries are not defined in the deps.lock file: %s', implode(', ', $unlocked)));
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
        return 'Deps files entries check';
    }
}
