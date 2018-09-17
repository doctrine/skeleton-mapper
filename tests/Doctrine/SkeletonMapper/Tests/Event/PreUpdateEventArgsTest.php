<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Event;

use Doctrine\SkeletonMapper\Event\PreUpdateEventArgs;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class PreUpdateEventArgsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var stdClass */
    private $object;

    /** @var ChangeSet|MockObject */
    private $changeSet;

    /** @var PreUpdateEventArgs */
    private $event;

    public function testGetObjectChangeSet() : void
    {
        self::assertSame($this->changeSet, $this->event->getObjectChangeSet());
    }

    public function testHasChangedField() : void
    {
        $this->changeSet->expects(self::once())
            ->method('hasChangedField')
            ->with('username')
            ->will(self::returnValue(true));

        self::assertTrue($this->event->hasChangedField('username'));
    }

    public function testGetOldValue() : void
    {
        $change = new Change('username', 'jwage', 'jonwage');

        $this->changeSet->expects(self::once())
            ->method('getFieldChange')
            ->with('username')
            ->will(self::returnValue($change));

        self::assertEquals('jwage', $this->event->getOldValue('username'));
    }

    public function testGetOldValueReturnsNull() : void
    {
        $this->changeSet->expects(self::once())
            ->method('getFieldChange')
            ->with('username')
            ->will(self::returnValue(null));

        self::assertNull($this->event->getOldValue('username'));
    }

    public function testGetNewValue() : void
    {
        $change = new Change('username', 'jwage', 'jonwage');

        $this->changeSet->expects(self::once())
            ->method('getFieldChange')
            ->with('username')
            ->will(self::returnValue($change));

        self::assertEquals('jonwage', $this->event->getNewValue('username'));
    }

    public function testGetNewValueReturnsNull() : void
    {
        $this->changeSet->expects(self::once())
            ->method('getFieldChange')
            ->with('username')
            ->will(self::returnValue(null));

        self::assertNull($this->event->getNewValue('username'));
    }

    public function testSetNewValue() : void
    {
        $change = new Change('username', 'jwage', 'jonwage');

        $this->changeSet->expects(self::any())
            ->method('getFieldChange')
            ->with('username')
            ->will(self::returnValue($change));

        $this->event->setNewValue('username', 'jonathan');

        self::assertEquals('jonathan', $this->event->getNewValue('username'));
    }

    public function testSetNewValueForUnchangedField() : void
    {
        $change = new Change('username', null, 'jonwage');

        $this->changeSet->expects(self::once())
            ->method('getFieldChange')
            ->with('username')
            ->will(self::returnValue(null));

        $this->changeSet->expects(self::once())
            ->method('addChange')
            ->with($change);

        $this->event->setNewValue('username', 'jonwage');
    }

    protected function setUp() : void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->object = new stdClass();

        $this->changeSet = $this->createMock(ChangeSet::class);

        $this->event = new PreUpdateEventArgs($this->object, $this->objectManager, $this->changeSet);
    }
}
