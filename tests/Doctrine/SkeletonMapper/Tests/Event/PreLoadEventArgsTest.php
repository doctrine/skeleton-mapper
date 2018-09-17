<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\Event\PreLoadEventArgs;
use PHPUnit\Framework\TestCase;

class PreLoadEventArgsTest extends TestCase
{
    private $objectManager;
    private $event;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new \stdClass();
        $this->data = array();

        $this->event = new PreLoadEventArgs($this->object, $this->objectManager, $this->data);
    }

    public function testGetData()
    {
        $data = &$this->event->getData();

        $data['test'] = true;

        $this->assertEquals($data, $this->event->getData());
    }
}
