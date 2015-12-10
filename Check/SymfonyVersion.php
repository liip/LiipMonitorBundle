<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\HttpKernel\Kernel;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

/**
 * Checks the version of this app against the latest stable release.
 *
 * @author Roderik van der Veer <roderik@vanderveer.be>
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SymfonyVersion implements CheckInterface
{
    const PACKAGIST_URL = 'https://packagist.org/packages/symfony/symfony.json';
    const VERSION_CHECK_URL = 'http://symfony.com/roadmap.json?version=%s';

    /**
     * {@inheritdoc}
     */
    public function check()
    {
        $currentBranch = Kernel::MAJOR_VERSION.'.'.Kernel::MINOR_VERSION;

        // use symfony.com version checker to see if current branch is still maintained
        $response = $this->getResponseAndDecode(sprintf(self::VERSION_CHECK_URL, $currentBranch));

        if (!isset($response['eol']) || !isset($response['is_eoled'])) {
            throw new \Exception('Invalid response from Symfony version checker.');
        }

        $endOfLife = \DateTime::createFromFormat('m/Y', $response['eol'])->format('F, Y');

        if (true === $response['is_eoled']) {
            return new Failure(sprintf('Symfony branch "%s" reached it\'s end of life in %s.', $currentBranch, $endOfLife));
        }

        $currentVersion = Kernel::VERSION;
        $latestRelease = $this->getLatestVersion($currentBranch); // eg. 2.0.12

        if (version_compare($currentVersion, $latestRelease) < 0) {
            return new Warning(sprintf('There is a new release - update to %s from %s.', $latestRelease, $currentVersion));
        }

        return new Success(sprintf('Your current Symfony branch reaches it\'s end of life in %s.', $endOfLife));
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Symfony version';
    }

    /**
     * @param string $branch
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getLatestVersion($branch)
    {
        $response = $this->getResponseAndDecode(self::PACKAGIST_URL);

        if (!isset($response['package']['versions'])) {
            throw new \Exception('Invalid response from packagist.');
        }

        $branch = 'v'.$branch;

        // filter out branches and versions without current minor version
        $versions = array_filter(
            $response['package']['versions'],
            function ($value) use ($branch) {
                $value = $value['version'];

                if (stripos($value, 'PR') || stripos($value, 'RC') && stripos($value, 'BETA')) {
                    return false;
                }

                return 0 === strpos($value, $branch);
            }
        );

        // just get versions
        $versions = array_keys($versions);

        // sort tags
        usort($versions, 'version_compare');

        // reverse to ensure latest is first
        $versions = array_reverse($versions);

        return str_replace('v', '', $versions[0]);
    }

    /**
     * @param $url
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getResponseAndDecode($url)
    {
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => "User-Agent: LiipMonitorBundle\r\n",
            ),
        );

        $array = json_decode(file_get_contents($url, false, stream_context_create($opts)), true);

        if (empty($array)) {
            throw new \Exception(sprintf('Invalid response from "%s".', $url));
        }

        return $array;
    }
}
