<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\HttpKernel\Kernel;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

/**
 * Checks the version of this website against the latest stable release.
 *
 * @author Roderik van der Veer <roderik@vanderveer.be>
 */
class SymfonyVersion implements CheckInterface
{
    /**
     * {@inheritdoc}
     */
    public function check()
    {
        $currentVersion = Kernel::VERSION;
        $latestRelease = $this->getLatestSymfonyVersion(); // eg. 2.0.12

        if (version_compare($currentVersion, $latestRelease) >= 0) {
            return new Success();
        }

        return new Warning(sprintf('Update to %s from %s.', $latestRelease, $currentVersion));
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Symfony version';
    }

    private function getLatestSymfonyVersion()
    {
        // Get GitHub JSON request

        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: LiipMonitorBundle\r\n"
            )
        );

        $context  = stream_context_create($opts);

        $githubUrl = 'https://api.github.com/repos/symfony/symfony/tags';
        $githubJSONResponse = file_get_contents($githubUrl, false, $context);

        // Convert it to a PHP object

        $githubResponseArray = json_decode($githubJSONResponse, true);
        if (empty($githubResponseArray)) {
            throw new \Exception("No valid response or no tags received from GitHub.");
        }

        $tags = array();

        foreach ($githubResponseArray as $tag) {
            $tags[] = $tag['name'];
        }

        // Sort tags

        usort($tags, "version_compare");

        // Filter out non final tags

        $filteredTagList = array_filter($tags, function ($tag) {
                return !stripos($tag, "PR") && !stripos($tag, "RC") && !stripos($tag, "BETA");
            });

        // The first one is the last stable release for Symfony 2

        $reverseFilteredTagList = array_reverse($filteredTagList);

        return str_replace("v", "", $reverseFilteredTagList[0]);
    }
}
