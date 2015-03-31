<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\Events;
use PHPUnit_Framework_TestCase;

abstract class BaseImplementationTest extends PHPUnit_Framework_TestCase
{
    protected $basicObjectHydrator;
    protected $classMetadataFactory;
    protected $objectFactory;
    protected $objectRepositoryFactory;
    protected $objectPersisterFactory;
    protected $objectIdentityMap;
    protected $eventManager;
    protected $userClassMetadata;
    protected $userRepository;
    protected $userPersister;
    protected $objectManager;
    protected $unitOfWork;
    protected $users;
    protected $testClassName = 'Doctrine\SkeletonMapper\Tests\Model\User';
    protected $eventTester;

    abstract protected function setUpImplementation();
    abstract protected function createUserDataRepository();
    abstract protected function createUserRepository();
    abstract protected function createUserPersister();

    protected function setUp()
    {
        $this->setUpImplementation();
        $this->setUpCommon();
        $this->userDataRepository = $this->createUserDataRepository();
        $this->userRepository = $this->createUserRepository();
        $this->userPersister = $this->createUserPersister();
        $this->registerServices();
    }

    protected function setUpCommon()
    {
        $this->eventTester = new EventTester();

        $events = array(
            Events::preRemove,
            Events::postRemove,
            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
            Events::postUpdate,
            Events::preLoad,
            Events::postLoad,
            Events::preFlush,
            Events::onFlush,
            Events::postFlush,
            Events::onClear,
        );

        $this->eventManager = new EventManager();
        foreach ($events as $event) {
            $this->eventManager->addEventListener($event, $this->eventTester);
        }

        $this->basicObjectHydrator = new SkeletonMapper\Hydrator\BasicObjectHydrator();
        $this->classMetadataFactory = new SkeletonMapper\Mapping\ClassMetadataFactory();
        $this->objectFactory = new SkeletonMapper\ObjectFactory();
        $this->objectRepositoryFactory = new SkeletonMapper\Repository\ObjectRepositoryFactory();
        $this->objectPersisterFactory = new SkeletonMapper\Persister\ObjectPersisterFactory();
        $this->objectIdentityMap = new SkeletonMapper\ObjectIdentityMap(
            $this->objectRepositoryFactory, $this->classMetadataFactory
        );

        // user class metadata
        $this->userClassMetadata = new SkeletonMapper\Mapping\ClassMetadata($this->testClassName);
        $this->userClassMetadata->identifier = array('_id');
        $this->userClassMetadata->identifierFieldNames = array('id');
        $this->userClassMetadata->mapField(array(
            'name' => '_id',
            'fieldName' => 'id',
        ));
        $this->userClassMetadata->mapField(array(
            'fieldName' => 'username',
        ));
        $this->userClassMetadata->mapField(array(
            'fieldName' => 'password',
        ));

        foreach ($events as $event) {
            $this->userClassMetadata->addLifecycleCallback($event, $event);
        }

        $this->classMetadataFactory->setMetadataFor(
            $this->testClassName, $this->userClassMetadata
        );

        $this->objectManager = new SkeletonMapper\ObjectManager(
            $this->objectRepositoryFactory,
            $this->objectPersisterFactory,
            $this->objectIdentityMap,
            $this->classMetadataFactory,
            $this->eventManager
        );
        $this->unitOfWork = $this->objectManager->getUnitOfWork();
    }

    protected function registerServices()
    {
        $this->objectRepositoryFactory->addObjectRepository(
            $this->testClassName, $this->userRepository
        );
        $this->objectPersisterFactory->addObjectPersister(
            $this->testClassName, $this->userPersister
        );
    }

    public function testGetClassMetadata()
    {
        $class = $this->objectManager->getClassMetadata($this->testClassName);

        $fieldMappings = array(
            'id' => array(
                'name' => '_id',
                'fieldName' => 'id',
            ),
            'username' => array(
                'name' => 'username',
                'fieldName' => 'username',
            ),
            'password' => array(
                'name' => 'password',
                'fieldName' => 'password',
            ),
        );

        $this->assertEquals($this->testClassName, $class->getName());
        $this->assertEquals(array('_id'), $class->getIdentifier());
        $this->assertEquals(array('id'), $class->getIdentifierFieldNames());
        $this->assertInstanceOf('ReflectionClass', $class->getReflectionClass());

        $this->assertTrue($class->isIdentifier('id'));
        $this->assertFalse($class->isIdentifier('username'));

        $this->assertTrue($class->hasField('username'));
        $this->assertFalse($class->hasField('nope'));

        $this->assertEquals(array('id', 'username', 'password'), $class->getFieldNames());
        $this->assertEquals($fieldMappings, $class->getFieldMappings());
    }

    public function testFind()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);

        $this->assertInstanceOf($this->testClassName, $user1);

        $this->assertEquals(1, $user1->getId());
        $this->assertEquals('jwage', $user1->getUsername());
        $this->assertEquals('password', $user1->getPassword());

        $user2 = $this->objectManager->find($this->testClassName, 2);

        $this->assertInstanceOf($this->testClassName, $user2);

        $this->assertSame($user2, $this->objectManager->find($this->testClassName, 2));

        $this->assertEquals(2, $user2->getId());
        $this->assertEquals('romanb', $user2->getUsername());
        $this->assertEquals('password', $user2->getPassword());
    }

    public function testFindAll()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);
        $user2 = $this->objectManager->find($this->testClassName, 2);

        $users = $this->objectManager
            ->getRepository($this->testClassName)
            ->findAll();

        $this->assertSame(array($user1, $user2), $users);
    }

    public function testFindBy()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);

        $users = $this->objectManager
            ->getRepository($this->testClassName)
            ->findBy(array('username' => 'jwage'));

        $this->assertSame(array($user1), $users);
    }

    public function testIdentityMap()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);
        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertSame($user1, $user2);
    }

    public function testPersist()
    {
        $user = $this->createTestObject();
        $user->setId(3);
        $user->setUsername('benjamin');
        $user->setPassword('password');

        $this->assertEquals(2, $this->users->count());

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->assertEquals(3, $this->users->count());
        $this->assertSame($user, $this->objectManager->find($this->testClassName, 3));
    }

    public function testUpdates()
    {
        $user = $this->objectManager->find($this->testClassName, 1);
        $user->setUsername('jonwage');

        $this->objectManager->update($user);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertEquals('jonwage', $user2->getUsername());
    }

    public function testRemove()
    {
        $user = $this->objectManager->find($this->testClassName, 2);

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $this->assertEquals(1, $this->users->count());

        $this->assertNull($this->objectManager->find($this->testClassName, 2));
    }

    public function testRefresh()
    {
        $user = $this->objectManager->find($this->testClassName, 1);

        $user->setPassword('yeehaw');

        $this->objectManager->refresh($user);

        $this->assertEquals('password', $user->getPassword());
    }

    public function testClear()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);

        $this->objectManager->clear($this->testClassName);

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertNotSame($user1, $user2);

        $this->objectManager->clear();

        $user3 = $this->objectManager->find($this->testClassName, 1);

        $this->assertNotSame($user2, $user3);

        $user = $this->createTestObject();
        $user->setId(10);

        $this->objectManager->persist($user);
        $this->objectManager->clear($this->testClassName);
        $this->objectManager->flush();

        $this->assertNull($this->objectManager->find($this->testClassName, 10));

        $user = $this->createTestObject();
        $user->setId(10);

        $this->objectManager->persist($user);
        $this->objectManager->clear();
        $this->objectManager->flush();

        $this->assertNull($this->objectManager->find($this->testClassName, 10));
    }

    public function testDetach()
    {
        $user1 = $this->objectManager->find($this->testClassName, 1);

        $this->objectManager->detach($user1);

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertNotSame($user1, $user2);
    }

    public function testMerge()
    {
        $user1 = $this->createTestObject();
        $user1->setId(1);
        $user1->setUsername('jonwage');
        $user1->setPassword('password');

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->objectManager->merge($user1);

        $this->assertEquals('jonwage', $user2->getUsername());
    }

    public function testContains()
    {
        $user = $this->createTestObject();
        $user->setId(3);

        $this->assertFalse($this->objectManager->contains($user));

        $this->objectManager->persist($user);

        $this->assertTrue($this->objectManager->contains($user));

        $this->objectManager->flush();

        $this->assertTrue($this->objectManager->contains($user));

        $this->objectManager->clear();

        $this->assertFalse($this->objectManager->contains($user));
    }

    public function testEvents()
    {
        $user = $this->createTestObject();
        $user->setId(3);

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $expected = array(
            Events::prePersist,
            Events::preFlush,
            Events::onFlush,
            Events::postPersist,
            Events::postFlush,
        );

        $this->assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = array();

        $user->setUsername('jmikola');
        $this->objectManager->update($user);
        $this->objectManager->flush();

        $expected = array(
            Events::preUpdate,
            Events::preFlush,
            Events::onFlush,
            Events::postUpdate,
            Events::postFlush,
        );

        $this->assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = array();
        $this->objectManager->clear();

        $expected = array(
            Events::onClear,
        );

        $this->assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = array();

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $expected = array(
            Events::preRemove,
            Events::preFlush,
            Events::onFlush,
            Events::postRemove,
            Events::postFlush,
        );

        $this->assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = array();

        $user = $this->objectManager->find($this->testClassName, 1);

        $expected = array(
            Events::preLoad,
            Events::postLoad,
        );

        $this->assertEquals($expected, $this->eventTester->called);
    }

    public function testLifecycleCallbacks()
    {
        $user = $this->createTestObject();
        $user->setId(3);

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $expected = array(
            Events::prePersist,
            Events::preFlush,
            Events::postPersist,
        );

        $this->assertEquals($expected, $user->called);

        $user->called = array();

        $user->setUsername('jmikola');
        $this->objectManager->update($user);
        $this->objectManager->flush();

        $expected = array(
            Events::preUpdate,
            Events::preFlush,
            Events::postUpdate,
        );

        $this->assertEquals($expected, $user->called);

        $user->called = array();

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $expected = array(
            Events::preRemove,
            Events::preFlush,
            Events::postRemove,
        );

        $this->assertEquals($expected, $user->called);

        $user->called = array();

        $user = $this->objectManager->find($this->testClassName, 1);

        $expected = array(
            Events::preLoad,
            Events::postLoad,
        );

        $this->assertEquals($expected, $user->called);
    }

    public function testPropertyChangedListeners()
    {
        $user = $this->objectManager->find($this->testClassName, 1);
        $user->setUsername('changed');

        $this->assertEquals(
            array('username' => array('jwage', 'changed')),
            $this->unitOfWork->getObjectChangeSet($user)
        );

        $this->objectManager->flush();
        $this->objectManager->clear();

        $this->assertEquals(
            array(),
            $this->unitOfWork->getObjectChangeSet($user)
        );

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertEquals('changed', $user2->getUsername());

        $user3 = $this->createTestObject();
        $user3->setId(3);
        $user3->setUsername('another');

        $this->assertEquals(
            array(),
            $this->unitOfWork->getObjectChangeSet($user3)
        );

        $this->objectManager->flush();
        $this->objectManager->clear();

        $this->assertNull($this->objectManager->find($this->testClassName, 3));

        $this->objectManager->persist($user3);
        $this->objectManager->flush();

        $this->assertNotNull($this->objectManager->find($this->testClassName, 3));

        $user3->setUsername('changed');

        $this->assertEquals(
            array('username' => array('another', 'changed')),
            $this->unitOfWork->getObjectChangeSet($user3)
        );

        $this->objectManager->flush();
        $this->objectManager->clear();

        $user3 = $this->objectManager->find($this->testClassName, 3);

        $this->assertEquals('changed', $user3->getUsername());

        $user3->setUsername('testing');

        $this->assertEquals(
            array('username' => array('changed', 'testing')),
            $this->unitOfWork->getObjectChangeSet($user3)
        );

        $this->objectManager->clear();
        $this->objectManager->flush();

        $this->assertEquals(
            array(),
            $this->unitOfWork->getObjectChangeSet($user)
        );

        $user3 = $this->objectManager->find($this->testClassName, 3);

        $this->assertEquals('changed', $user3->getUsername());
    }

    public function testIdentifierGeneration()
    {
        $user = $this->createTestObject();
        $user->setUsername('jwage');

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->assertEquals(3, $user->getId());
    }

    public function testClassMetadata()
    {
        $object = $this->createTestObject();
        $object->setId(1);

        $class = $this->classMetadataFactory->getMetadataFor($this->testClassName);
        $this->assertEquals($this->testClassName, $class->name);
        $this->assertTrue($class->hasField('id'));
        $this->assertTrue($class->hasField('username'));
        $this->assertTrue($class->hasField('password'));
        $this->assertFalse($class->hasAssociation('password'));
        $this->assertFalse($class->isSingleValuedAssociation('password'));
        $this->assertFalse($class->isCollectionValuedAssociation('password'));
        $this->assertTrue($class->isIdentifier('id'));
        $this->assertFalse($class->isIdentifier('username'));
        $this->assertEquals(array('_id'), $class->getIdentifier());
        $this->assertEquals(array('id'), $class->getIdentifierFieldNames());
        $this->assertEquals(array('id', 'username', 'password'), $class->getFieldNames());
        $this->assertInstanceOf('ReflectionClass', $class->getReflectionClass());
        $this->assertEquals(array(
            'id' => array(
                'name' => '_id',
                'fieldName' => 'id',
            ),
            'username' => array(
                'name' => 'username',
                'fieldName' => 'username',
            ),
            'password' => array(
                'name' => 'password',
                'fieldName' => 'password',
            ),
        ), $class->getFieldMappings());
        $this->assertEquals(array(), $class->getAssociationNames());
        $this->assertNull($class->getTypeOfField('username'));
        $this->assertEquals(array('id' => 1), $class->getIdentifierValues($object));
    }

    private function createTestObject()
    {
        $className = $this->testClassName;

        return new $className();
    }
}

class EventTester
{
    public $called = array();

    public function __call($method, array $arguments)
    {
        $this->called[] = $method;
    }
}
