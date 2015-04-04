<?php

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\SkeletonMapper\DataRepository\BasicObjectDataRepository;
use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */
class BasicObjectDataRepositoryTest extends PHPUnit_Framework_TestCase
{
    private $objectManager;
    private $objectDataRepository;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->getMock();

        $this->objectDataRepository = new TestBasicObjectDataRepository(
            $this->objectManager,
            'TestClassName'
        );
    }

    public function testGetClassName()
    {
        $this->assertEquals('TestClassName', $this->objectDataRepository->getClassName());
    }

    public function testFind()
    {
        $class = $this->getMockBuilder('Doctrine\SkeletonMapper\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $class->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue(array('_id' => 1)));

        $this->objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->with('TestClassName')
            ->will($this->returnValue($class));

        $this->assertEquals(array('username' => 'jwage'), $this->objectDataRepository->find(1));
    }
}


class TestBasicObjectDataRepository extends BasicObjectDataRepository
{
    public function findAll()
    {
        return array(array('username' => 'jwage'));
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return array(array('username' => 'jwage'));
    }

    public function findOneBy(array $criteria)
    {
        return array('username' => 'jwage');
    }
}
