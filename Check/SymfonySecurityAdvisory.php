<?php

namespace Liip\MonitorBundle\Check;

use Exception;
use Liip\Monitor\Check\Check;
use Liip\Monitor\Result\CheckResult;
use Guzzle\Http\Client;

/**
 * Checks installed dependencies against Symfony Security Advisory database.
 *
 * Add this to your config.yml
 *
 *     monitor.check.symfony_security_advisory:
 *         class: Liip\MonitorBundle\Check\SymfonySecurityAdvisory
 *         arguments:
 *             - %kernel.root_dir%
 *         tags:
 *             - { name: liip_monitor.check }
 *
 * @author Baldur Rensch <brensch@gmail.com>
 */
class SymfonySecurityAdvisoryCheck extends Check
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
     * {@inheritdoc}
     */
    public function check()
    {
        try {
            $advisories = $this->checkSymfonyAdvisories();
            if (empty($advisories)) {
                $result = $this->buildResult('OK', CheckResult::OK);
            } else {
                $result = $this->buildResult('Advisories for ' . count($advisories) . ' packages', CheckResult::WARNING);
            }
        } catch (Exception $e) {
            $result = $this->buildResult($e->getMessage(), CheckResult::UNKNOWN);
        }

        return $result;
    }

    private function checkSymfonyAdvisories()
    {
        $fileName = $this->kernelRootDir. '/../composer.lock';
        if (!file_exists($fileName)) {
            throw new Exception("No composer lock file found");
        }

        $client = new Client('https://security.sensiolabs.org');

        $request = $client->post(
            'check_lock',
            array('Accept' => 'application/json'),
            array('lock' => '@' . $fileName),
        );

        $response = $request->send();
        $json = $response->json();

        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Symfony security advisory';
    }
}