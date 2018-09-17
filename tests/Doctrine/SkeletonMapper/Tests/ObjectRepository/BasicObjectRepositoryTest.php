<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class BasicObjectRepositoryTest extends TestCase
{
    private $objectManager;
    private $objectDataRepository;
    private $objectFactory;
    private $hydrator;
    private $eventManager;
    private $classMetadata;
    private $repository;
    private $testClassName = 'Doctrine\SkeletonMapper\Tests\Functional\BasicObjectRepositoryTestModel';

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectDataRepository = $this->getMockBuilder('Doctrine\SkeletonMapper\DataRepository\ObjectDataRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectFactory = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->hydrator = $this->getMockBuilder('Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventManager = $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->classMetadata = new ClassMetadata($this->testClassName);
        $this->classMetadata->identifier = array('id');
        $this->classMetadata->identifierFieldNames = array('id');
        $this->classMetadata->mapField(array(
            'fieldName' => 'id',
        ));

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->testClassName)
            ->will($this->returnValue($this->classMetadata));

        $this->repository = new BasicObjectRepository(
            $this->objectManager,
            $this->objectDataRepository,
            $this->objectFactory,
            $this->hydrator,
            $this->eventManager,
            $this->testClassName
        );
    }

    public function testGetObjectIdentifier()
    {
        $object = new BasicObjectRepositoryTestModel();
        $object->id = 1;

        $data = array('id' => 1);
        $this->assertEquals($data, $this->repository->getObjectIdentifier($object));
    }

    public function testGetObjectIdentifierFromData()
    {
        $data = array('id' => 1);
        $this->assertEquals($data, $this->repository->getObjectIdentifierFromData($data));
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Not implemented.
     */
    public function testMerge()
    {
        $this->repository->merge(new \stdClass());
    }
}

class BasicObjectRepositoryTestModel
{
    public $id;
}
