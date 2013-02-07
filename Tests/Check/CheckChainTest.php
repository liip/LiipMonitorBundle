<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection;

use Liip\Monitor\Check\CheckChain;

class CheckChainTest extends \PHPUnit_Framework_TestCase
{

    public function testAddCheck()
    {
        $check = $this->getMock('Liip\Monitor\Check\CheckInterface');

        $checkChain = new CheckChain();
        $checkChain->addCheck('foo', $check);

        $this->assertEquals(1, count($checkChain->getChecks()));
        $this->assertInstanceOf('Liip\Monitor\Check\CheckInterface', $checkChain->getCheckById('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionInGetCheckById()
    {
        $checkChain = new CheckChain();
        $checkChain->getCheckById('fake');
    }

    public function testCheckGroups()
    {
        $checkOne = $check = $this->getMock('Liip\Monitor\Check\CheckInterface');

        $checkOne
            ->expects($this->exactly(2))
            ->method('getGroup')
            ->will($this->returnValue('foo'))
        ;

        $checkTwo = $check = $this->getMock('Liip\Monitor\Check\CheckInterface');

        $checkTwo
            ->expects($this->exactly(2))
            ->method('getGroup')
            ->will($this->returnValue('bar'))
        ;

        $checkChain = new CheckChain();
        $checkChain->addCheck('foo_id', $checkOne);
        $checkChain->addCheck('bar_id', $checkTwo);

        $this->assertEquals(array('foo', 'bar'), $checkChain->getGroups());
    }
}