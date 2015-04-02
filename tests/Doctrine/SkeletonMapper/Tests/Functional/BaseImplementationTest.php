<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\Tests\Model\Profile;
use Doctrine\SkeletonMapper\Tests\Model\ProfileRepository;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
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
    protected $profileRepository;
    protected $userPersister;
    protected $profilePersister;
    protected $objectManager;
    protected $unitOfWork;
    protected $users;
    protected $profiles;
    protected $usersTester;
    protected $profilesTester;
    protected $profileClassName = 'Doctrine\SkeletonMapper\Tests\Model\Profile';
    protected $userClassName = 'Doctrine\SkeletonMapper\Tests\Model\User';
    protected $eventTester;

    abstract protected function setUpImplementation();

    abstract protected function createUserDataRepository();
    abstract protected function createUserPersister();

    abstract protected function createProfileDataRepository();
    abstract protected function createProfilePersister();

    protected function setUp()
    {
        $this->setUpImplementation();
        $this->setUpCommon();

        $this->userDataRepository = $this->createUserDataRepository();
        $this->userRepository = $this->createUserRepository();
        $this->userPersister = $this->createUserPersister();

        $this->profileDataRepository = $this->createProfileDataRepository();
        $this->profileRepository = $this->createProfileRepository();
        $this->profilePersister = $this->createProfilePersister();

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

        $this->classMetadataFactory = new SkeletonMapper\Mapping\ClassMetadataFactory();
        $this->objectFactory = new SkeletonMapper\ObjectFactory();
        $this->objectRepositoryFactory = new SkeletonMapper\ObjectRepository\ObjectRepositoryFactory();
        $this->objectPersisterFactory = new SkeletonMapper\Persister\ObjectPersisterFactory();
        $this->objectIdentityMap = new SkeletonMapper\ObjectIdentityMap(
            $this->objectRepositoryFactory, $this->classMetadataFactory
        );

        // user class metadata
        $this->userClassMetadata = $this->classMetadataFactory->getMetadataFor($this->userClassName);

        foreach ($events as $event) {
            $this->userClassMetadata->addLifecycleCallback($event, $event);
        }

        $this->classMetadataFactory->setMetadataFor(
            $this->userClassName, $this->userClassMetadata
        );

        $this->objectManager = new SkeletonMapper\ObjectManager(
            $this->objectRepositoryFactory,
            $this->objectPersisterFactory,
            $this->objectIdentityMap,
            $this->classMetadataFactory,
            $this->eventManager
        );

        $this->basicObjectHydrator = new SkeletonMapper\Hydrator\BasicObjectHydrator(
            $this->objectManager
        );
        $this->unitOfWork = $this->objectManager->getUnitOfWork();
    }

    protected function registerServices()
    {
        $this->objectRepositoryFactory->addObjectRepository(
            $this->userClassName, $this->userRepository
        );
        $this->objectRepositoryFactory->addObjectRepository(
            $this->profileClassName, $this->profileRepository
        );

        $this->objectPersisterFactory->addObjectPersister(
            $this->userClassName, $this->userPersister
        );
        $this->objectPersisterFactory->addObjectPersister(
            $this->profileClassName, $this->profilePersister
        );
    }

    public function testGetClassMetadata()
    {
        $class = $this->objectManager->getClassMetadata($this->userClassName);

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
            'profile' => array(
                'name' => 'profileId',
                'fieldName' => 'profile',
            ),
        );

        $this->assertEquals($this->userClassName, $class->getName());
        $this->assertEquals(array('_id'), $class->getIdentifier());
        $this->assertEquals(array('id'), $class->getIdentifierFieldNames());
        $this->assertInstanceOf('ReflectionClass', $class->getReflectionClass());

        $this->assertTrue($class->isIdentifier('id'));
        $this->assertFalse($class->isIdentifier('username'));

        $this->assertTrue($class->hasField('username'));
        $this->assertFalse($class->hasField('nope'));

        $this->assertEquals(array('id', 'username', 'password', 'profile'), $class->getFieldNames());
        $this->assertEquals($fieldMappings, $class->getFieldMappings());
    }

    public function testFind()
    {
        $user1 = $this->objectManager->find($this->userClassName, 1);

        $this->assertInstanceOf($this->userClassName, $user1);

        $this->assertEquals(1, $user1->getId());
        $this->assertEquals('jwage', $user1->getUsername());
        $this->assertEquals('password', $user1->getPassword());

        $user2 = $this->objectManager->find($this->userClassName, 2);

        $this->assertInstanceOf($this->userClassName, $user2);

        $this->assertSame($user2, $this->objectManager->find($this->userClassName, 2));

        $this->assertEquals(2, $user2->getId());
        $this->assertEquals('romanb', $user2->getUsername());
        $this->assertEquals('password', $user2->getPassword());
    }

    public function testFindAll()
    {
        $user1 = $this->objectManager->find($this->userClassName, 1);
        $user2 = $this->objectManager->find($this->userClassName, 2);

        $users = $this->objectManager
            ->getRepository($this->userClassName)
            ->findAll();

        $this->assertSame(array($user1, $user2), $users);
    }

    public function testFindBy()
    {
        $user1 = $this->objectManager->find($this->userClassName, 1);

        $users = $this->objectManager
            ->getRepository($this->userClassName)
            ->findBy(array('username' => 'jwage'));

        $this->assertSame(array($user1), $users);
    }

    public function testIdentityMap()
    {
        $user1 = $this->objectManager->find($this->userClassName, 1);
        $user2 = $this->objectManager->find($this->userClassName, 1);

        $this->assertSame($user1, $user2);
    }

    public function testPersist()
    {
        $user = $this->createTestObject();
        $user->setId(3);
        $user->setUsername('benjamin');
        $user->setPassword('password');

        $this->assertEquals(2, $this->usersTester->count());

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->assertEquals(3, $this->usersTester->count());
        $this->assertSame($user, $this->objectManager->find($this->userClassName, 3));
    }

    public function testUpdates()
    {
        $user = $this->objectManager->find($this->userClassName, 1);
        $user->setUsername('jonwage');

        $this->objectManager->flush();
        $this->objectManager->clear();

        $user2 = $this->objectManager->find($this->userClassName, 1);

        $this->assertEquals('jonwage', $user2->getUsername());
    }

    public function testRemove()
    {
        $user = $this->objectManager->find($this->userClassName, 2);

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $this->assertEquals(1, $this->usersTester->count());

        $this->assertNull($this->objectManager->find($this->userClassName, 2));
    }

    public function testRefresh()
    {
        $user = $this->objectManager->find($this->userClassName, 1);

        $user->setPassword('yeehaw');

        $this->objectManager->refresh($user);

        $this->assertEquals('password', $user->getPassword());
    }

    public function testClear()
    {
        $user1 = $this->objectManager->find($this->userClassName, 1);

        $this->objectManager->clear($this->userClassName);

        $user2 = $this->objectManager->find($this->userClassName, 1);

        $this->assertNotSame($user1, $user2);

        $this->objectManager->clear();

        $user3 = $this->objectManager->find($this->userClassName, 1);

        $this->assertNotSame($user2, $user3);

        $user = $this->createTestObject();
        $user->setId(10);

        $this->objectManager->persist($user);
        $this->objectManager->clear($this->userClassName);
        $this->objectManager->flush();

        $this->assertNull($this->objectManager->find($this->userClassName, 10));

        $user = $this->createTestObject();
        $user->setId(10);

        $this->objectManager->persist($user);
        $this->objectManager->clear();
        $this->objectManager->flush();

        $this->assertNull($this->objectManager->find($this->userClassName, 10));
    }

    public function testDetach()
    {
        $user1 = $this->objectManager->find($this->userClassName, 1);

        $this->objectManager->detach($user1);

        $user2 = $this->objectManager->find($this->userClassName, 1);

        $this->assertNotSame($user1, $user2);
    }

    public function testMerge()
    {
        $user1 = $this->createTestObject();
        $user1->setId(1);
        $user1->setUsername('jonwage');
        $user1->setPassword('password');

        $user2 = $this->objectManager->find($this->userClassName, 1);

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

        $user = $this->objectManager->find($this->userClassName, 1);

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

        $user = $this->objectManager->find($this->userClassName, 1);

        $expected = array(
            Events::preLoad,
            Events::postLoad,
        );

        $this->assertEquals($expected, $user->called);
    }

    public function testPropertyChangedListeners()
    {
        $user = $this->objectManager->find($this->userClassName, 1);
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

        $user2 = $this->objectManager->find($this->userClassName, 1);

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

        $this->assertNull($this->objectManager->find($this->userClassName, 3));

        $this->objectManager->persist($user3);
        $this->objectManager->flush();

        $this->assertNotNull($this->objectManager->find($this->userClassName, 3));

        $user3->setUsername('changed');

        $this->assertEquals(
            array('username' => array('another', 'changed')),
            $this->unitOfWork->getObjectChangeSet($user3)
        );

        $this->objectManager->flush();
        $this->objectManager->clear();

        $user3 = $this->objectManager->find($this->userClassName, 3);

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

        $user3 = $this->objectManager->find($this->userClassName, 3);

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

        $class = $this->classMetadataFactory->getMetadataFor($this->userClassName);
        $this->assertEquals($this->userClassName, $class->name);
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
        $this->assertEquals(array('id', 'username', 'password', 'profile'), $class->getFieldNames());
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
            'profile' => array(
                'name' => 'profileId',
                'fieldName' => 'profile',
            ),
        ), $class->getFieldMappings());
        $this->assertEquals(array(), $class->getAssociationNames());
        $this->assertNull($class->getTypeOfField('username'));
        $this->assertEquals(array('_id' => 1), $class->getIdentifierValues($object));
    }

    public function testOnlyUpdatesWhatChanged()
    {
        $user = $this->objectManager->find($this->userClassName, 1);
        $user->setUsername('changed');

        $this->usersTester->set(1, 'password', 'changed password');

        $this->objectManager->flush();
        $this->objectManager->clear();

        $user = $this->objectManager->find($this->userClassName, 1);
        $this->assertEquals('changed', $user->getUsername());

        $data = $this->usersTester->find(1);

        $this->assertEquals('changed password', $data['password']);
    }

    public function testReferences()
    {
        $user = $this->objectManager->find($this->userClassName, 1);

        $profile = new Profile();
        $profile->setName('Jonathan H. Wage');
        $user->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $profile = $this->objectManager->find($this->profileClassName, 1);
        $this->assertEquals('Jonathan H. Wage', $profile->getName());

        $user = $this->objectManager->find($this->userClassName, 1);
        $this->assertSame($profile, $user->getProfile());

        $profile = new Profile();
        $profile->setName('John Caplan');

        $user = $this->createTestObject();
        $user->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $user = $this->objectManager->find($this->userClassName, $user->getId());
        $this->assertEquals('John Caplan', $user->getProfile()->getName());
    }

    protected function createUserRepository()
    {
        return new UserRepository(
            $this->objectManager,
            $this->userDataRepository,
            $this->objectFactory,
            $this->basicObjectHydrator,
            $this->eventManager,
            $this->userClassName
        );
    }

    protected function createProfileRepository()
    {
        return new ProfileRepository(
            $this->objectManager,
            $this->profileDataRepository,
            $this->objectFactory,
            $this->basicObjectHydrator,
            $this->eventManager,
            $this->profileClassName
        );
    }

    private function createTestObject()
    {
        $className = $this->userClassName;

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
