<?php

namespace Doctrine\SkeletonMapper\Tests\Persister;

use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\Persister\HttpObjectPersister;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class HttpObjectPersisterTest extends TestCase
{
    private $objectManager;
    private $client;
    private $persister;
    private $testClassName = 'Doctrine\SkeletonMapper\Tests\Persister\HttpObjectPersisterTestModel';

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

        $this->client = $this->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->persister = new HttpObjectPersister(
            $this->objectManager, $this->client, $this->testClassName, 'http://localhost/users'
        );
    }

    public function testPersistObject()
    {
        $object = new HttpObjectPersisterTestModel();

        $results = array('username' => 'jwage', 'id' => 1);

        $response = $this->getMockBuilder('GuzzleHttp\Message\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('json')
            ->will($this->returnValue($results));

        $this->client->expects($this->once())
            ->method('post')
            ->with('http://localhost/users', array('body' => array('username' => 'jwage')))
            ->will($this->returnValue($response));

        $this->assertEquals(array('username' => 'jwage', 'id' => 1), $this->persister->persistObject($object));
    }

    public function testUpdateObject()
    {
        $this->objects[1] = array(
            'id' => 1,
            'username' => 'jwage',
        );

        $results = array('username' => 'jwage', 'id' => 1);

        $object = new HttpObjectPersisterTestModel();

        $changeSet = new ChangeSet($object, array(new Change('username', 'jwage', 'jonwage')));

        $response = $this->getMockBuilder('GuzzleHttp\Message\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('json')
            ->will($this->returnValue($results));

        $this->client->expects($this->once())
            ->method('put')
            ->with('http://localhost/users/1', array('body' => array('username' => 'jonwage')))
            ->will($this->returnValue($response));

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

        $this->assertEquals(array('username' => 'jwage', 'id' => 1), $this->persister->updateObject($object, $changeSet));
    }

    public function testRemoveObject()
    {
        $this->objects[1] = array(
            'id' => 1,
            'username' => 'jwage',
        );

        $results = array('username' => 'jwage', 'id' => 1);

        $object = new HttpObjectPersisterTestModel();

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

        $this->client->expects($this->once())
            ->method('delete')
            ->with('http://localhost/users/1');

        $this->persister->removeObject($object);
    }
}

class HttpObjectPersisterTestModel implements PersistableInterface
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
