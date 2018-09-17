<?php

namespace Doctrine\SkeletonMapper\Tests\Persister;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\Persister\ArrayObjectPersister;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ArrayObjectPersisterTest extends TestCase
{
    private $objectManager;
    private $objects;
    private $persister;
    private $testClassName = 'Doctrine\SkeletonMapper\Tests\Persister\ArrayObjectPersisterTestModel';

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $classMetadata = new ClassMetadata($this->testClassName);
        $classMetadata->identifier = array('id');

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->testClassName)
            ->will($this->returnValue($classMetadata));

        $this->objects = new ArrayCollection();
        $this->persister = new ArrayObjectPersister(
            $this->objectManager, $this->objects, $this->testClassName
        );
    }

    public function testPersistObject()
    {
        $object = new ArrayObjectPersisterTestModel();

        $this->assertEquals(array('username' => 'jwage', 'id' => 1), $this->persister->persistObject($object));
        $this->assertEquals(array(1 => array('username' => 'jwage', 'id' => 1)), $this->objects->toArray());
    }

    public function testUpdateObject()
    {
        $this->objects[1] = array(
            'id' => 1,
            'username' => 'jwage',
        );

        $object = new ArrayObjectPersisterTestModel();

        $changeSet = new ChangeSet($object, array(new Change('username', 'jwage', 'jonwage')));

        $repository = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->once())
            ->method('getObjectIdentifier')
            ->with($object)
            ->will($this->returnValue(array('id' => 1)));

        $this->objectManager->expects($this->once())
            ->method('getRepository')
            ->with($this->testClassName)
            ->will($this->returnValue($repository));

        $this->assertEquals(array('username' => 'jonwage', 'id' => 1), $this->persister->updateObject($object, $changeSet));
        $this->assertEquals(array(1 => array('username' => 'jonwage', 'id' => 1)), $this->objects->toArray());
    }

    public function testRemoveObject()
    {
        $this->objects[1] = array(
            'id' => 1,
            'username' => 'jwage',
        );

        $object = new ArrayObjectPersisterTestModel();

        $repository = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->once())
            ->method('getObjectIdentifier')
            ->with($object)
            ->will($this->returnValue(array('id' => 1)));

        $this->objectManager->expects($this->once())
            ->method('getRepository')
            ->with($this->testClassName)
            ->will($this->returnValue($repository));

        $this->persister->removeObject($object);

        $this->assertCount(0, $this->objects);
    }
}

class ArrayObjectPersisterTestModel implements PersistableInterface
{
    public function preparePersistChangeSet()
    {
        return array('username' => 'jwage');
    }

    public function prepareUpdateChangeSet(ChangeSet $changeSet)
    {
        $changes = array();

        foreach ($changeSet->getChanges() as $change) {
            $changes[$change->getPropertyName()] = $change->getNewValue();
        }

        return $changes;
    }
}
