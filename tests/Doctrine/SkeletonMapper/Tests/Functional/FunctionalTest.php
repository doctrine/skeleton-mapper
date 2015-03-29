<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\User;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserHydrator;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserPersister;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserRepository;
use PHPUnit_Framework_TestCase;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    protected $objectManager;
    protected $users;
    protected $testClassName = 'Doctrine\SkeletonMapper\Tests\TestImplementation\User\User';

    protected function setUp()
    {
        $this->users = new ArrayCollection(array(
            1 => array(
                'id' => 1,
                'username' => 'jwage',
                'password' => 'password',
            ),
            2 => array(
                'id' => 2,
                'username' => 'romanb',
                'password' => 'password',
            ),
        ));

        $objectFactory = new SkeletonMapper\ObjectFactory();
        $objectRepositoryFactory = new SkeletonMapper\ObjectRepositoryFactory();
        $objectPersisterFactory = new SkeletonMapper\ObjectPersisterFactory();
        $objectIdentityMap = new SkeletonMapper\ObjectIdentityMap($objectRepositoryFactory);

        // user class metadata
        $userClassMetadata = new SkeletonMapper\Mapping\ClassMetadata($this->testClassName);
        $userClassMetadata->identifier = array('id');
        $userClassMetadata->autoMapFields();

        $classMetadataFactory = new SkeletonMapper\Mapping\ClassMetadataFactory();
        $classMetadataFactory->setMetadataFor($this->testClassName, $userClassMetadata);

        // user data repo
        $userDataRepository = new UserDataRepository($this->users);

        // user hydrator
        $userHydrator = new UserHydrator();

        // user repo
        $userRepository = new UserRepository(
            $userDataRepository,
            $objectFactory,
            $userHydrator,
            $objectIdentityMap
        );
        $objectRepositoryFactory->addObjectRepository($this->testClassName, $userRepository);

        // user persister
        $userPersister = new UserPersister($objectIdentityMap, $this->users);
        $objectPersisterFactory->addObjectPersister($this->testClassName, $userPersister);

        $unitOfWork = new SkeletonMapper\UnitOfWork(
            $objectPersisterFactory,
            $objectRepositoryFactory,
            $objectIdentityMap
        );

        $this->objectManager = new SkeletonMapper\ObjectManager(
            $objectRepositoryFactory,
            $objectPersisterFactory,
            $unitOfWork,
            $classMetadataFactory
        );
    }

    public function testGetClassMetadata()
    {
        $class = $this->objectManager->getClassMetadata($this->testClassName);

        $fieldMappings = array(
            'id' => array(
                'fieldName' => 'id',
            ),
            'username' => array(
                'fieldName' => 'username',
            ),
            'password' => array(
                'fieldName' => 'password',
            ),
        );

        $this->assertEquals($this->testClassName, $class->getName());
        $this->assertEquals(array('id'), $class->getIdentifier());
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

        $this->assertEquals(1, $user1->id);
        $this->assertEquals('jwage', $user1->username);
        $this->assertEquals('password', $user1->password);

        $user2 = $this->objectManager->find($this->testClassName, 2);

        $this->assertInstanceOf($this->testClassName, $user2);

        $this->assertSame($user2, $this->objectManager->find($this->testClassName, 2));

        $this->assertEquals(2, $user2->id);
        $this->assertEquals('romanb', $user2->username);
        $this->assertEquals('password', $user2->password);
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
        $user = new User();
        $user->id = 3;
        $user->username = 'benjamin';
        $user->password = 'password';

        $this->assertCount(2, $this->users);

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->assertCount(3, $this->users);
        $this->assertSame($user, $this->objectManager->find($this->testClassName, 3));
    }

    public function testUpdates()
    {
        $user = $this->objectManager->find($this->testClassName, 1);
        $user->username = 'jonwage';

        $this->objectManager->update($user);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->assertEquals('jonwage', $user2->username);
    }

    public function testRemove()
    {
        $user = $this->objectManager->find($this->testClassName, 2);

        $this->objectManager->remove($user);
        $this->objectManager->flush();

        $this->assertCount(1, $this->users);

        $this->assertNull($this->objectManager->find($this->testClassName, 2));
    }

    public function testRefresh()
    {
        $user = $this->objectManager->find($this->testClassName, 1);

        $userData = $this->users[1];
        $userData['password'] = 'changed';
        $this->users[1] = $userData;

        $this->objectManager->refresh($user);

        $this->assertEquals('changed', $user->password);
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

        $user = new User();
        $user->id = 10;

        $this->objectManager->persist($user);
        $this->objectManager->clear($this->testClassName);
        $this->objectManager->flush();

        $this->assertNull($this->objectManager->find($this->testClassName, 10));

        $user = new User();
        $user->id = 10;

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
        $user1 = new User();
        $user1->id = 1;
        $user1->username = 'jonwage';
        $user1->password = 'password';

        $user2 = $this->objectManager->find($this->testClassName, 1);

        $this->objectManager->merge($user1);

        $this->assertEquals('jonwage', $user2->username);
    }

    public function testContains()
    {
        $user = new User();
        $user->id = 1;

        $this->assertFalse($this->objectManager->contains($user));

        $this->objectManager->persist($user);

        $this->assertTrue($this->objectManager->contains($user));

        $this->objectManager->clear();

        $this->assertFalse($this->objectManager->contains($user));

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->assertTrue($this->objectManager->contains($user));

        $this->objectManager->remove($user);

        $this->assertTrue($this->objectManager->contains($user));

        $this->objectManager->flush();

        $this->assertFalse($this->objectManager->contains($user));
    }
}
