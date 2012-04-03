<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection;

use Liip\MonitorBundle\Check\CheckChain;

class CheckChainTest extends \PHPUnit_Framework_TestCase
{

    public function testAddCheck()
    {
        $check = $this->getMock('Liip\MonitorBundle\Check\CheckInterface');

        $checkChain = new CheckChain();
        $checkChain->addCheck('foo', $check);

        $this->assertEquals(1, count($checkChain->getChecks()));
        $this->assertInstanceOf('Liip\MonitorBundle\Check\CheckInterface', $checkChain->getCheckById('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionInGetCheckById()
    {
        $checkChain = new CheckChain();
        $checkChain->getCheckById('fake');
    }

}