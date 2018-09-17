<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\Hydrator\BasicObjectHydrator;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class BasicObjectHydratorTest extends TestCase
{
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->hydrator = new BasicObjectHydrator($this->objectManager);
    }

    public function testHydrate()
    {
        $object = new HydratableObject();
        $data = array('test');

        $this->hydrator->hydrate($object, $data);

        $this->assertEquals($data, $object->data);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage stdClass must implement HydratableInterface
     */
    public function testHydrateThrowsInvalidArgumentException()
    {
        $this->hydrator->hydrate(new \stdClass(), array());
    }
}

class HydratableObject implements HydratableInterface
{
    public $data;

    public function hydrate(array $data, ObjectManagerInterface $objectManager)
    {
        $this->data = $data;
    }
}
