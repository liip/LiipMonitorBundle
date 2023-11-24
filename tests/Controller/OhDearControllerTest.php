<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class OhDearControllerTest extends KernelTestCase
{
    use HasBrowser;

    /**
     * @test
     * @group slow
     */
    public function run_checks(): void
    {
        $content = $this->browser()
            ->throwExceptions()
            ->get('/oh-dear-health-check', [
                'headers' => ['OH-DEAR-HEALTH-CHECK-SECRET' => 'secret'],
            ])
            ->assertSuccessful()
            ->json()
            ->decoded()
        ;

        $this->assertIsArray($content);
        $this->assertCount(21, $content['checkResults']);
        $this->assertSame('Check Service 1', $content['checkResults'][0]['label']);
        $this->assertSame('ok', $content['checkResults'][0]['status']);
    }

    /**
     * @test
     */
    public function not_found_if_secret_missing(): void
    {
        $this->browser()
            ->get('/oh-dear-health-check')
            ->assertStatus(404)
        ;
    }

    /**
     * @test
     */
    public function not_found_if_secret_mismatch(): void
    {
        $this->browser()
            ->get('/oh-dear-health-check', [
                'headers' => ['OH-DEAR-HEALTH-CHECK-SECRET' => 'invalid'],
            ])
            ->assertStatus(404)
        ;
    }
}
