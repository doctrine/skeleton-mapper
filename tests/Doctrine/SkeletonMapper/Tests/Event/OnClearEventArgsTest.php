<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\Event\OnClearEventArgs;
use PHPUnit\Framework\TestCase;

class OnClearEventArgsTest extends TestCase
{
    private $objectManager;
    private $event;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = new OnClearEventArgs($this->objectManager, 'TestClassName');
    }

    public function testGetObjectClass()
    {
        $this->assertEquals('TestClassName', $this->event->getObjectClass());
    }

    public function testClearsAllObjects()
    {
        $this->assertFalse($this->event->clearsAllObjects());

        $event = new OnClearEventArgs($this->objectManager);

        $this->assertTrue($event->clearsAllObjects());
    }
}
