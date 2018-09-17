<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\Event\PreUpdateEventArgs;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use PHPUnit\Framework\TestCase;

class PreUpdateEventArgsTest extends TestCase
{
    private $objectManager;
    private $event;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new \stdClass();

        $this->changeSet = $this->getMockBuilder('Doctrine\SkeletonMapper\UnitOfWork\ChangeSet')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = new PreUpdateEventArgs($this->object, $this->objectManager, $this->changeSet);
    }

    public function testGetObjectChangeSet()
    {
        $this->assertSame($this->changeSet, $this->event->getObjectChangeSet());
    }

    public function testHasChangedField()
    {
        $this->changeSet->expects($this->once())
            ->method('hasChangedField')
            ->with('username')
            ->will($this->returnValue(true));

        $this->assertTrue($this->event->hasChangedField('username'));
    }

    public function testGetOldValue()
    {
        $change = new Change('username', 'jwage', 'jonwage');

        $this->changeSet->expects($this->once())
            ->method('getFieldChange')
            ->with('username')
            ->will($this->returnValue($change));

        $this->assertEquals('jwage', $this->event->getOldValue('username'));
    }

    public function testGetOldValueReturnsNull()
    {
        $this->changeSet->expects($this->once())
            ->method('getFieldChange')
            ->with('username')
            ->will($this->returnValue(null));

        $this->assertNull($this->event->getOldValue('username'));
    }

    public function testGetNewValue()
    {
        $change = new Change('username', 'jwage', 'jonwage');

        $this->changeSet->expects($this->once())
            ->method('getFieldChange')
            ->with('username')
            ->will($this->returnValue($change));

        $this->assertEquals('jonwage', $this->event->getNewValue('username'));
    }

    public function testGetNewValueReturnsNull()
    {
        $this->changeSet->expects($this->once())
            ->method('getFieldChange')
            ->with('username')
            ->will($this->returnValue(null));

        $this->assertNull($this->event->getNewValue('username'));
    }

    public function testSetNewValue()
    {
        $change = new Change('username', 'jwage', 'jonwage');

        $this->changeSet->expects($this->any())
            ->method('getFieldChange')
            ->with('username')
            ->will($this->returnValue($change));

        $this->event->setNewValue('username', 'jonathan');

        $this->assertEquals('jonathan', $this->event->getNewValue('username'));
    }

    public function testSetNewValueForUnchangedField()
    {
        $change = new Change('username', null, 'jonwage');

        $this->changeSet->expects($this->once())
            ->method('getFieldChange')
            ->with('username')
            ->will($this->returnValue(null));

        $this->changeSet->expects($this->once())
            ->method('addChange')
            ->with($change);

        $this->event->setNewValue('username', 'jonwage');
    }
}
