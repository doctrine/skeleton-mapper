<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Event;

use Doctrine\SkeletonMapper\Event\PreLoadEventArgs;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class PreLoadEventArgsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var stdClass */
    private $object;

    /** @var mixed[] */
    private $data;

    /** @var PreLoadEventArgs */
    private $event;

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
