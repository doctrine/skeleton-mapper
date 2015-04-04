<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepository;
use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */
class ObjectRepositoryTest extends PHPUnit_Framework_TestCase
{
    private $objectManager;
    private $objectDataRepository;
    private $objectFactory;
    private $hydrator;
    private $eventManager;
    private $classMetadata;
    private $repository;

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

        $this->classMetadata = $this->getMockBuilder('Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->with('TestClassName')
            ->will($this->returnValue($this->classMetadata));

        $this->repository = new TestObjectRepository(
            $this->objectManager,
            $this->objectDataRepository,
            $this->objectFactory,
            $this->hydrator,
            $this->eventManager,
            'TestClassName'
        );
    }

    public function testGetClassName()
    {
        $this->assertEquals('TestClassName', $this->repository->getClassName());
    }

    public function testFind()
    {
        $data = array('username' => 'jwage');

        $this->objectDataRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue($data));

        $this->objectManager->expects($this->once())
            ->method('getOrCreateObject')
            ->with('TestClassName', $data)
            ->will($this->returnValue(new \stdClass()));

        $object = $this->repository->find(1);
        $this->assertEquals(new \stdClass(), $object);
    }

    public function testFindAll()
    {
        $data = array(
            array('username' => 'jwage'),
            array('username' => 'romanb'),
        );

        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $this->objectDataRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($data));

        $this->objectManager->expects($this->at(0))
            ->method('getOrCreateObject')
            ->with('TestClassName', $data[0])
            ->will($this->returnValue($object1));

        $this->objectManager->expects($this->at(1))
            ->method('getOrCreateObject')
            ->with('TestClassName', $data[1])
            ->will($this->returnValue($object2));

        $objects = $this->repository->findAll(1);
        $this->assertSame(array($object1, $object2), $objects);
    }

    public function testFindBy()
    {
        $data = array(
            array('username' => 'jwage'),
            array('username' => 'romanb'),
        );

        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $this->objectDataRepository->expects($this->once())
            ->method('findBy')
            ->with(array())
            ->will($this->returnValue($data));

        $this->objectManager->expects($this->at(0))
            ->method('getOrCreateObject')
            ->with('TestClassName', $data[0])
            ->will($this->returnValue($object1));

        $this->objectManager->expects($this->at(1))
            ->method('getOrCreateObject')
            ->with('TestClassName', $data[1])
            ->will($this->returnValue($object2));

        $objects = $this->repository->findBy(array());
        $this->assertSame(array($object1, $object2), $objects);
    }

    public function testFindOneBy()
    {
        $data = array('username' => 'jwage');
        $criteria = array('username' => 'jwage');

        $this->objectDataRepository->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->will($this->returnValue($data));

        $this->objectManager->expects($this->once())
            ->method('getOrCreateObject')
            ->with('TestClassName', $data)
            ->will($this->returnValue(new \stdClass()));

        $object = $this->repository->findOneBy($criteria);
        $this->assertEquals(new \stdClass(), $object);
    }

    public function testRefresh()
    {
        $data = array('username' => 'jwage');

        $this->objectDataRepository->expects($this->once())
            ->method('find')
            ->with(array('id' => 1))
            ->will($this->returnValue($data));

        $object = new \stdClass();

        $this->hydrator->expects($this->once())
            ->method('hydrate')
            ->with($object, $data);

        $this->repository->refresh($object);
    }

    public function testCreate()
    {
        $object = new \stdClass();

        $this->objectFactory->expects($this->once())
            ->method('create')
            ->with('stdClass')
            ->will($this->returnValue($object));

        $this->assertSame($object, $this->repository->create('stdClass'));
    }
}

class TestObjectRepository extends ObjectRepository
{
    public function getClassMetadata()
    {
        return $this->class;
    }

    public function getObjectIdentifier($object)
    {
        return array('id' => 1);
    }

    public function getObjectIdentifierFromData(array $data)
    {
        return array('id' => 1);
    }

    public function merge($object)
    {
    }
}
