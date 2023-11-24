<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Fixture\Controller;

use Liip\Monitor\Controller\OhDearController as BaseOhDearController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[Route('/oh-dear-health-check')]
#[AsController]
final class OhDearController extends BaseOhDearController
{
}
