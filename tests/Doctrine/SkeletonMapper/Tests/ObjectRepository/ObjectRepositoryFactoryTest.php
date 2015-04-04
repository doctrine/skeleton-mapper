<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactory;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */
class ObjectRepositoryFactoryTest extends PHPUnit_Framework_TestCase
{
    private $factory;

    protected function setUp()
    {
        $this->factory = new ObjectRepositoryFactory();
    }

    public function testAddObjectRepository()
    {
        $repository = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface')
            ->getMock();

        $this->factory->addObjectRepository('TestClassName', $repository);

        $this->assertSame($repository, $this->factory->getRepository('TestClassName'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedMessage ObjectRepository with class name DoesNotExist was not found
     */
    public function testGetRepositoryThrowsInvalidArgumentException()
    {
        $this->factory->getRepository('DoesNotExist');
    }
}
