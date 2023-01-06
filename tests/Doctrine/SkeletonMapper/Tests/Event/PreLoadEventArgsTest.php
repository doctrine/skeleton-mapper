<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Event;

use Doctrine\SkeletonMapper\Event\PreLoadEventArgs;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class PreLoadEventArgsTest extends TestCase
{
    private ObjectManagerInterface $objectManager;

    private stdClass $object;

    /** @var mixed[] */
    private array $data;

    private PreLoadEventArgs $event;

    public function testGetData(): void
    {
        $data = &$this->event->getData();

        $data['test'] = true;

        self::assertEquals($data, $this->event->getData());
    }

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->object = new stdClass();
        $this->data   = [];

        $this->event = new PreLoadEventArgs($this->object, $this->objectManager, $this->data);
    }
}
