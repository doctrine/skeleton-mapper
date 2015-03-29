<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\User;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserHydrator;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserPersister;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserRepository;

class MongoDBImplementationTest extends BaseImplementationTest
{
    protected $objectManager;
    protected $users;
    protected $testClassName = 'Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\User';

    protected function setUp()
    {
        $mongo = version_compare(phpversion('mongo'), '1.3.0', '<')
            ? new \Mongo()
            : new \MongoClient();

        $this->users = $mongo->selectDb('test')->selectCollection('users');

        $this->users->drop();

        $this->users->batchInsert(array(
            array(
                '_id' => 1,
                'username' => 'jwage',
                'password' => 'password',
            ),
            array(
                '_id' => 2,
                'username' => 'romanb',
                'password' => 'password',
            ),
        ));

        $objectFactory = new SkeletonMapper\ObjectFactory();
        $objectRepositoryFactory = new SkeletonMapper\Repository\ObjectRepositoryFactory();
        $objectPersisterFactory = new SkeletonMapper\Persister\ObjectPersisterFactory();
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
}
