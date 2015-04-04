<?php

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\DataRepository\MongoDBObjectDataRepository;
use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */
class MongoDBObjectDataRepositoryTest extends PHPUnit_Framework_TestCase
{
    private $objectManager;
    private $mongoCollection;
    private $objectDataRepository;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->getMock();

        $this->mongoCollection = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectDataRepository = new TestMongoDBObjectDataRepository(
            $this->objectManager,
            $this->mongoCollection,
            'TestClassName'
        );
    }

    public function testGetMongoCollection()
    {
        $this->assertSame(
            $this->mongoCollection,
            $this->objectDataRepository->getMongoCollection()
        );
    }

    public function testFindAll()
    {
        $cursor = $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mongoCollection->expects($this->once())
            ->method('find')
            ->with(array())
            ->will($this->returnValue($cursor));

        $this->assertSame(array(), $this->objectDataRepository->findAll());
    }

    public function testFindBy()
    {
        $criteria = array('username' => 'jwage');
        $orderBy = array('username' => 'desc');
        $limit = 20;
        $offset = 20;

        $cursor = $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();

        $cursor->expects($this->once())
            ->method('sort')
            ->with($orderBy);

        $cursor->expects($this->once())
            ->method('limit')
            ->with($limit);

        $cursor->expects($this->once())
            ->method('skip')
            ->with($offset);

        $this->mongoCollection->expects($this->once())
            ->method('find')
            ->with($criteria)
            ->will($this->returnValue($cursor));

        $this->assertSame(array(), $this->objectDataRepository->findBy($criteria, $orderBy, $limit, $offset));
    }
}

class TestMongoDBObjectDataRepository extends MongoDBObjectDataRepository
{
}
