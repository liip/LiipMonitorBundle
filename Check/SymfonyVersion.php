<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\HttpKernel\Kernel;
use ZendDiagnostics\Check\AbstractCheck;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

/**
 * Checks the version of this website against the latest stable release.
 *
 * Add this to your config.yml
 *
 *     monitor.check.symfony_version:
 *         class: Liip\MonitorBundle\Check\SymfonyVersionCheck
 *         tags:
 *             - { name: liip_monitor.check }
 *
 * @author Roderik van der Veer <roderik@vanderveer.be>
 */
class SymfonyVersion extends AbstractCheck
{
    /**
     * {@inheritdoc}
     */
    public function check()
    {
        try {
            $latestRelease = $this->getLatestSymfonyVersion(); // eg. 2.0.12
            $currentVersion = Kernel::VERSION;
            if (version_compare($currentVersion, $latestRelease) < 0) {
                return new Warning('Update to ' . $latestRelease . ' from ' . $currentVersion);
            }
        } catch (\Exception $e) {
            return new Warning($e->getMessage());
        }

        return new Success();
    }

    private function getLatestSymfonyVersion()
    {
        // Get GitHub JSON request

        $githubUrl = 'https://api.github.com/repos/symfony/symfony/tags';
        $githubJSONResponse = file_get_contents($githubUrl);

        // Convert it to a PHP object

        $githubResponseArray = json_decode($githubJSONResponse, true);
        if (empty($githubResponseArray)) {
            throw new Exception("No valid response or no tags received from GitHub.");
        }

        $tags = array();

        foreach ($githubResponseArray as $tag) {
            $tags[] = $tag['name'];
        }

        // Sort tags

        usort($tags, "version_compare");

        // Filter out non final tags

        $filteredTagList = array_filter($tags, function($tag) {
            return !stripos($tag, "PR") && !stripos($tag, "RC") && !stripos($tag, "BETA");
        });

        // The first one is the last stable release for Symfony 2

        $reverseFilteredTagList = array_reverse($filteredTagList);

        return str_replace("v", "", $reverseFilteredTagList[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Symfony version';
    }
}
