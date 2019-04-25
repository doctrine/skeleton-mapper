<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventManager;
use Doctrine\Persistence\Mapping\ClassMetadata as BaseClassMetadata;
use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\DataRepository\ObjectDataRepository;
use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\Persister\ObjectPersister;
use Doctrine\SkeletonMapper\Tests\DataTesterInterface;
use Doctrine\SkeletonMapper\Tests\Model\Address;
use Doctrine\SkeletonMapper\Tests\Model\Group;
use Doctrine\SkeletonMapper\Tests\Model\GroupRepository;
use Doctrine\SkeletonMapper\Tests\Model\Profile;
use Doctrine\SkeletonMapper\Tests\Model\ProfileRepository;
use Doctrine\SkeletonMapper\Tests\Model\User;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
use Doctrine\SkeletonMapper\UnitOfWork;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use PHPUnit\Framework\TestCase;
use function assert;
use function is_int;

abstract class BaseImplementationTest extends TestCase
{
    /** @var SkeletonMapper\Hydrator\BasicObjectHydrator */
    protected $basicObjectHydrator;

    /** @var SkeletonMapper\Mapping\ClassMetadataFactory */
    protected $classMetadataFactory;

    /** @var SkeletonMapper\Mapping\ClassMetadataInstantiator */
    protected $classMetadataInstantiator;

    /** @var SkeletonMapper\ObjectFactory */
    protected $objectFactory;

    /** @var SkeletonMapper\ObjectRepository\ObjectRepositoryFactory */
    protected $objectRepositoryFactory;

    /** @var SkeletonMapper\Persister\ObjectPersisterFactory */
    protected $objectPersisterFactory;

    /** @var SkeletonMapper\ObjectIdentityMap */
    protected $objectIdentityMap;

    /** @var EventManager */
    protected $eventManager;

    /** @var ClassMetadataInterface|BaseClassMetadata */
    protected $userClassMetadata;

    /** @var SkeletonMapper\ObjectManager */
    protected $objectManager;

    /** @var UnitOfWork */
    protected $unitOfWork;

    /** @var User[]|ArrayCollection */
    protected $users;

    /** @var Profile[]|ArrayCollection */
    protected $profiles;

    /** @var Group[]|ArrayCollection */
    protected $groups;

    /** @var DataTesterInterface */
    protected $usersTester;

    /** @var DataTesterInterface */
    protected $profilesTester;

    /** @var DataTesterInterface */
    protected $groupsTester;

    /** @var string */
    protected $userClassName = User::class;

    /** @var EventTester */
    protected $eventTester;

    /** @var ObjectDataRepository */
    protected $userDataRepository;

    /** @var UserRepository */
    protected $userRepository;

    /** @var ObjectPersister */
    protected $userPersister;

    /** @var ObjectDataRepository */
    protected $profileDataRepository;

    /** @var ProfileRepository */
    protected $profileRepository;

    /** @var ObjectPersister */
    protected $profilePersister;

    /** @var ObjectDataRepository */
    protected $groupDataRepository;

    /** @var GroupRepository */
    protected $groupRepository;

    /** @var ObjectPersister */
    protected $groupPersister;

    abstract protected function setUpImplementation() : void;

    abstract protected function createUserDataRepository() : ObjectDataRepository;

    abstract protected function createUserPersister() : ObjectPersister;

    abstract protected function createProfileDataRepository() : ObjectDataRepository;

    abstract protected function createProfilePersister() : ObjectPersister;

    abstract protected function createGroupDataRepository() : ObjectDataRepository;

    abstract protected function createGroupPersister() : ObjectPersister;

    protected function setUp() : void
    {
        $this->setUpImplementation();
        $this->setUpCommon();

        $this->userDataRepository = $this->createUserDataRepository();
        $this->userRepository     = $this->createUserRepository();
        $this->userPersister      = $this->createUserPersister();

        $this->profileDataRepository = $this->createProfileDataRepository();
        $this->profileRepository     = $this->createProfileRepository();
        $this->profilePersister      = $this->createProfilePersister();

        $this->groupDataRepository = $this->createGroupDataRepository();
        $this->groupRepository     = $this->createGroupRepository();
        $this->groupPersister      = $this->createGroupPersister();

        $this->registerServices();
    }

    protected function setUpCommon() : void
    {
        $this->eventTester = new EventTester();

        $events = [
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
        ];

        $this->eventManager = new EventManager();
        foreach ($events as $event) {
            $this->eventManager->addEventListener($event, $this->eventTester);
        }

        $this->classMetadataInstantiator = new SkeletonMapper\Mapping\ClassMetadataInstantiator();
        $this->classMetadataFactory      = new SkeletonMapper\Mapping\ClassMetadataFactory($this->classMetadataInstantiator);
        $this->objectFactory             = new SkeletonMapper\ObjectFactory();
        $this->objectRepositoryFactory   = new SkeletonMapper\ObjectRepository\ObjectRepositoryFactory();
        $this->objectPersisterFactory    = new SkeletonMapper\Persister\ObjectPersisterFactory();
        $this->objectIdentityMap         = new SkeletonMapper\ObjectIdentityMap(
            $this->objectRepositoryFactory
        );

        // user class metadata
        $this->userClassMetadata = $this->classMetadataFactory->getMetadataFor(User::class);
        assert($this->userClassMetadata instanceof ClassMetadataInterface);

        foreach ($events as $event) {
            $this->userClassMetadata->addLifecycleCallback($event, $event);
        }

        $this->classMetadataFactory->setMetadataFor(
            User::class,
            $this->userClassMetadata
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
        $this->unitOfWork          = $this->objectManager->getUnitOfWork();
    }

    protected function registerServices() : void
    {
        $this->objectRepositoryFactory->addObjectRepository(
            User::class,
            $this->userRepository
        );
        $this->objectRepositoryFactory->addObjectRepository(
            Profile::class,
            $this->profileRepository
        );
        $this->objectRepositoryFactory->addObjectRepository(
            Group::class,
            $this->groupRepository
        );

        $this->objectPersisterFactory->addObjectPersister(
            User::class,
            $this->userPersister
        );
        $this->objectPersisterFactory->addObjectPersister(
            Profile::class,
            $this->profilePersister
        );
        $this->objectPersisterFactory->addObjectPersister(
            Group::class,
            $this->groupPersister
        );
    }

    public function testGetClassMetadata() : void
    {
        $class = $this->objectManager->getClassMetadata(User::class);
        assert($class instanceof ClassMetadataInterface);

        $fieldMappings = [
            'id' => [
                'name' => '_id',
                'fieldName' => 'id',
            ],
            'username' => [
                'name' => 'username',
                'fieldName' => 'username',
            ],
            'password' => [
                'name' => 'password',
                'fieldName' => 'password',
            ],
            'profile' => [
                'name' => 'profileId',
                'fieldName' => 'profile',
            ],
            'groups' => [
                'name' => 'groupIds',
                'fieldName' => 'groups',
            ],
        ];

        self::assertEquals(User::class, $class->getName());
        self::assertEquals(['_id'], $class->getIdentifier());
        self::assertEquals(['id'], $class->getIdentifierFieldNames());
        self::assertSame(User::class, $class->getReflectionClass()->getName());

        self::assertTrue($class->isIdentifier('id'));
        self::assertFalse($class->isIdentifier('username'));

        self::assertTrue($class->hasField('username'));
        self::assertFalse($class->hasField('nope'));

        self::assertEquals(['id', 'username', 'password', 'profile', 'groups'], $class->getFieldNames());
        self::assertEquals($fieldMappings, $class->getFieldMappings());
    }

    public function testFind() : void
    {
        $user1 = $this->findUser(1);

        self::assertEquals(1, $user1->getId());
        self::assertEquals('jwage', $user1->getUsername());
        self::assertEquals('password', $user1->getPassword());

        $user2 = $this->findUser(2);

        self::assertSame($user2, $this->findUser(2));

        self::assertEquals(2, $user2->getId());
        self::assertEquals('romanb', $user2->getUsername());
        self::assertEquals('password', $user2->getPassword());
    }

    public function testFindAll() : void
    {
        $user1 = $this->findUser(1);
        $user2 = $this->findUser(2);

        $users = $this->objectManager
            ->getRepository(User::class)
            ->findAll();

        self::assertSame([$user1, $user2], $users);
    }

    public function testFindBy() : void
    {
        $user1 = $this->findUser(1);

        $users = $this->objectManager
            ->getRepository(User::class)
            ->findBy(['username' => 'jwage']);

        self::assertSame([$user1], $users);
    }

    public function testIdentityMap() : void
    {
        $user1 = $this->findUser(1);
        $user2 = $this->findUser(1);

        self::assertSame($user1, $user2);
    }

    public function testPersist() : void
    {
        $user = $this->createTestObject();
        $user->setId(3);
        $user->setUsername('benjamin');
        $user->setPassword('password');

        self::assertEquals(2, $this->usersTester->count());

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        self::assertEquals(3, $this->usersTester->count());
        self::assertSame($user, $this->findUser(3));
    }

    public function testUpdates() : void
    {
        $user = $this->findUser(1);
        $user->setUsername('jonwage');

        $this->objectManager->flush();
        $this->objectManager->clear();

        $user2 = $this->findUser(1);

        self::assertEquals('jonwage', $user2->getUsername());
    }

    public function testRemove() : void
    {
        $user = $this->findUser(2);

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        self::assertEquals(1, $this->usersTester->count());

        self::assertNull($this->objectManager->find(User::class, 2));
    }

    public function testRefresh() : void
    {
        $user = $this->findUser(1);

        $user->setPassword('yeehaw');

        $this->objectManager->refresh($user);

        self::assertEquals('password', $user->getPassword());
    }

    public function testClear() : void
    {
        $user1 = $this->findUser(1);

        $this->objectManager->clear(User::class);

        $user2 = $this->findUser(1);

        self::assertNotSame($user1, $user2);

        $this->objectManager->clear();

        $user3 = $this->findUser(1);

        self::assertNotSame($user2, $user3);

        $user = $this->createTestObject();
        $user->setId(10);

        $this->objectManager->persist($user);
        $this->objectManager->clear(User::class);
        $this->objectManager->flush();

        self::assertNull($this->objectManager->find(User::class, 10));

        $user = $this->createTestObject();
        $user->setId(10);

        $this->objectManager->persist($user);
        $this->objectManager->clear();
        $this->objectManager->flush();

        self::assertNull($this->objectManager->find(User::class, 10));
    }

    public function testDetach() : void
    {
        $user1 = $this->findUser(1);

        $this->objectManager->detach($user1);

        $user2 = $this->findUser(1);

        self::assertNotSame($user1, $user2);
    }

    public function testMerge() : void
    {
        $user1 = $this->createTestObject();
        $user1->setId(1);
        $user1->setUsername('jonwage');
        $user1->setPassword('password');

        $user2 = $this->findUser(1);

        $this->objectManager->merge($user1);

        self::assertEquals('jonwage', $user2->getUsername());
    }

    public function testContains() : void
    {
        $user = $this->createTestObject();
        $user->setId(3);

        self::assertFalse($this->objectManager->contains($user));

        $this->objectManager->persist($user);

        self::assertTrue($this->objectManager->contains($user));

        $this->objectManager->flush();

        self::assertTrue($this->objectManager->contains($user));

        $this->objectManager->clear();

        self::assertFalse($this->objectManager->contains($user));
    }

    public function testEvents() : void
    {
        $user = $this->createTestObject();
        $user->setId(3);

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $expected = [
            Events::prePersist,
            Events::preFlush,
            Events::onFlush,
            Events::postPersist,
            Events::postFlush,
        ];

        self::assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = [];

        $user->setUsername('jmikola');
        $this->objectManager->flush();

        $expected = [
            Events::preUpdate,
            Events::preFlush,
            Events::onFlush,
            Events::postUpdate,
            Events::postFlush,
        ];

        self::assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = [];
        $this->objectManager->clear();

        $expected = [
            Events::onClear,
        ];

        self::assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = [];

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $expected = [
            Events::preRemove,
            Events::preFlush,
            Events::onFlush,
            Events::postRemove,
            Events::postFlush,
        ];

        self::assertEquals($expected, $this->eventTester->called);

        $this->eventTester->called = [];

        $user = $this->findUser(1);

        $expected = [
            Events::preLoad,
            Events::postLoad,
        ];

        self::assertEquals($expected, $this->eventTester->called);
    }

    public function testLifecycleCallbacks() : void
    {
        $user = $this->createTestObject();
        $user->setId(3);

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $expected = [
            Events::prePersist,
            Events::preFlush,
            Events::postPersist,
        ];

        self::assertEquals($expected, $user->called);

        $user->called = [];

        $user->setUsername('jmikola');
        $this->objectManager->flush();

        $expected = [
            Events::preUpdate,
            Events::preFlush,
            Events::postUpdate,
        ];

        self::assertEquals($expected, $user->called);

        $user->called = [];

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $expected = [
            Events::preRemove,
            Events::preFlush,
            Events::postRemove,
        ];

        self::assertEquals($expected, $user->called);

        $user->called = [];

        $user = $this->findUser(1);

        $expected = [
            Events::preLoad,
            Events::postLoad,
        ];

        self::assertEquals($expected, $user->called);
    }

    public function testPropertyChangedListeners() : void
    {
        $user = $this->findUser(1);
        $user->setUsername('changed');

        self::assertEquals(
            new ChangeSet($user, ['username' => new Change('username', 'jwage', 'changed')]),
            $this->unitOfWork->getObjectChangeSet($user)
        );

        $this->objectManager->flush();
        $this->objectManager->clear();

        self::assertEquals(
            new ChangeSet($user, []),
            $this->unitOfWork->getObjectChangeSet($user)
        );

        $user2 = $this->findUser(1);

        self::assertEquals('changed', $user2->getUsername());

        $user3 = $this->createTestObject();
        $user3->setId(3);
        $user3->setUsername('another');

        self::assertEquals(
            new ChangeSet($user3, []),
            $this->unitOfWork->getObjectChangeSet($user3)
        );

        $this->objectManager->flush();
        $this->objectManager->clear();

        self::assertNull($this->objectManager->find(User::class, 3));

        $this->objectManager->persist($user3);
        $this->objectManager->flush();

        self::assertNotNull($this->objectManager->find(User::class, 3));

        $user3->setUsername('changed');

        self::assertEquals(
            new ChangeSet($user3, ['username' => new Change('username', 'another', 'changed')]),
            $this->unitOfWork->getObjectChangeSet($user3)
        );

        $this->objectManager->flush();
        $this->objectManager->clear();

        $user3 = $this->findUser(3);

        self::assertEquals('changed', $user3->getUsername());

        $user3->setUsername('testing');

        self::assertEquals(
            new ChangeSet($user3, ['username' => new Change('username', 'changed', 'testing')]),
            $this->unitOfWork->getObjectChangeSet($user3)
        );

        $this->objectManager->clear();
        $this->objectManager->flush();

        self::assertEquals(
            new ChangeSet($user, []),
            $this->unitOfWork->getObjectChangeSet($user)
        );

        $user3 = $this->findUser(3);

        self::assertEquals('changed', $user3->getUsername());
    }

    public function testIdentifierGeneration() : void
    {
        $user = $this->createTestObject();
        $user->setUsername('jwage');

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        self::assertEquals(3, $user->getId());
    }

    public function testClassMetadata() : void
    {
        $object = $this->createTestObject();
        $object->setId(1);

        /** @var ClassMetadataInterface $class */
        $class = $this->classMetadataFactory->getMetadataFor(User::class);

        self::assertEquals(User::class, $class->getName());
        self::assertTrue($class->hasField('id'));
        self::assertTrue($class->hasField('username'));
        self::assertTrue($class->hasField('password'));
        self::assertFalse($class->hasAssociation('password'));
        self::assertFalse($class->isSingleValuedAssociation('password'));
        self::assertFalse($class->isCollectionValuedAssociation('password'));
        self::assertTrue($class->isIdentifier('id'));
        self::assertFalse($class->isIdentifier('username'));
        self::assertEquals(['_id'], $class->getIdentifier());
        self::assertEquals(['id'], $class->getIdentifierFieldNames());
        self::assertEquals(['id', 'username', 'password', 'profile', 'groups'], $class->getFieldNames());
        self::assertSame(User::class, $class->getReflectionClass()->getName());
        self::assertEquals([
            'id' => [
                'name' => '_id',
                'fieldName' => 'id',
            ],
            'username' => [
                'name' => 'username',
                'fieldName' => 'username',
            ],
            'password' => [
                'name' => 'password',
                'fieldName' => 'password',
            ],
            'profile' => [
                'name' => 'profileId',
                'fieldName' => 'profile',
            ],
            'groups' => [
                'name' => 'groupIds',
                'fieldName' => 'groups',
            ],
        ], $class->getFieldMappings());
        self::assertEquals([], $class->getAssociationNames());
        self::assertEquals('', $class->getTypeOfField('username'));
        self::assertEquals(['_id' => 1], $class->getIdentifierValues($object));
    }

    public function testOnlyUpdatesWhatChanged() : void
    {
        $user = $this->findUser(1);
        $user->setUsername('changed');

        $this->usersTester->set(1, 'password', 'changed password');

        $this->objectManager->flush();
        $this->objectManager->clear();

        $user = $this->findUser(1);
        self::assertEquals('changed', $user->getUsername());

        $data = $this->usersTester->find(1);

        self::assertEquals('changed password', $data['password']);
    }

    public function testReferences() : void
    {
        $user = $this->findUser(1);

        $profile = new Profile();
        $profile->setName('Jonathan H. Wage');
        $user->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $profile = $this->objectManager->find(Profile::class, 1);
        assert($profile instanceof Profile);

        self::assertEquals('Jonathan H. Wage', $profile->getName());

        $user = $this->findUser(1);
        self::assertSame($profile, $user->getProfile());

        $profile = new Profile();
        $profile->setName('John Caplan');

        $user = $this->createTestObject();
        $user->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->persist($user);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $id = $user->getId();
        assert(is_int($id));

        $user = $this->findUser($id);
        self::assertEquals('John Caplan', $user->getProfile()->getName());
    }

    public function testEmbeddedAddress() : void
    {
        $user = $this->findUser(1);

        $profile = new Profile();
        $address = new Address($profile);
        $address->setAddress1('273 Lake Terrace Dr.');
        $address->setCity('Hendersonville');
        $address->setState('TN');
        $address->setZip('37075');

        $profile->setAddress($address);
        $profile->setName('Jonathan H. Wage');
        $user->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $user = $this->findUser(1);

        self::assertEquals($address, $user->getProfile()->getAddress());

        $user->getProfile()->getAddress()->setState('Tennessee');

        $this->objectManager->flush();
        $this->objectManager->clear();

        $user = $this->findUser(1);

        self::assertEquals('Tennessee', $user->getProfile()->getAddress()->getState());
    }

    public function testReferenceMany() : void
    {
        $adminGroup = new Group('Admin');
        $techGroup  = new Group('Tech');
        $user       = $this->createTestObject();

        $this->objectManager->persist($adminGroup);
        $this->objectManager->persist($techGroup);
        $this->objectManager->persist($user);

        $user->setUsername('ryanweaver');
        $user->addGroup($adminGroup);
        $user->addGroup($techGroup);

        $this->objectManager->flush();
        $this->objectManager->clear();

        $id = $user->getId();
        assert(is_int($id));

        $user = $this->findUser($id);

        self::assertCount(2, $user->getGroups());

        $groups = $user->getGroups();

        self::assertEquals('Admin', $groups[0]->getName());
        self::assertEquals('Tech', $groups[1]->getName());

        $moderatorGroup = new Group('Moderator');
        $user->addGroup($moderatorGroup);

        $this->objectManager->persist($moderatorGroup);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $id = $user->getId();
        assert(is_int($id));

        $user = $this->findUser($id);

        self::assertCount(3, $user->getGroups());
        self::assertEquals('Moderator', $groups[2]->getName());
    }

    protected function createUserRepository() : UserRepository
    {
        return new UserRepository(
            $this->objectManager,
            $this->userDataRepository,
            $this->objectFactory,
            $this->basicObjectHydrator,
            $this->eventManager,
            User::class
        );
    }

    protected function createProfileRepository() : ProfileRepository
    {
        return new ProfileRepository(
            $this->objectManager,
            $this->profileDataRepository,
            $this->objectFactory,
            $this->basicObjectHydrator,
            $this->eventManager,
            Profile::class
        );
    }

    protected function createGroupRepository() : GroupRepository
    {
        return new GroupRepository(
            $this->objectManager,
            $this->groupDataRepository,
            $this->objectFactory,
            $this->basicObjectHydrator,
            $this->eventManager,
            Group::class
        );
    }

    private function createTestObject() : User
    {
        return new User();
    }

    private function findUser(int $id) : User
    {
        $user = $this->objectManager->find(User::class, $id);
        assert($user instanceof User);

        return $user;
    }
}

class EventTester
{
    /** @var string[] */
    public $called = [];

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $method, array $arguments) : void
    {
        $this->called[] = $method;
    }
}
