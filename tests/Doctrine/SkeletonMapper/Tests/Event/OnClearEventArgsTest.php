<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Event;

use Doctrine\SkeletonMapper\Event\OnClearEventArgs;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class OnClearEventArgsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var OnClearEventArgs */
    private $event;

    public function testGetObjectClass(): void
    {
        self::assertEquals('TestClassName', $this->event->getObjectClass());
    }

    public function testClearsAllObjects(): void
    {
        self::assertFalse($this->event->clearsAllObjects());

        $event = new OnClearEventArgs($this->objectManager);

        self::assertTrue($event->clearsAllObjects());
    }

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->event = new OnClearEventArgs($this->objectManager, 'TestClassName');
    }
}
