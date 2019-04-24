<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Hydrator;

use Doctrine\SkeletonMapper\Hydrator\BasicObjectHydrator;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class BasicObjectHydratorTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var BasicObjectHydrator */
    private $hydrator;

    public function testHydrate() : void
    {
        $object = new HydratableObject();
        $data   = ['key' => 'value'];

        $this->hydrator->hydrate($object, $data);

        self::assertEquals($data, $object->data);
    }

    protected function setUp() : void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->hydrator = new BasicObjectHydrator($this->objectManager);
    }
}

class HydratableObject implements HydratableInterface
{
    /** @var array<string, string> */
    public $data;

    /**
     * @param mixed[] $data
     */
    public function hydrate(array $data, ObjectManagerInterface $objectManager) : void
    {
        $this->data = $data;
    }
}
