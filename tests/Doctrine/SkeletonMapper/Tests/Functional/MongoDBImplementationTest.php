<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\User;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserHydrator;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserPersister;
use Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User\UserRepository;

class MongoDBImplementationTest extends BaseImplementationTest
{
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

        $eventManager = new EventManager();
        $classMetadataFactory = new SkeletonMapper\Mapping\ClassMetadataFactory();
        $objectFactory = new SkeletonMapper\ObjectFactory();
        $objectRepositoryFactory = new SkeletonMapper\Repository\ObjectRepositoryFactory();
        $objectPersisterFactory = new SkeletonMapper\Persister\ObjectPersisterFactory();
        $objectIdentityMap = new SkeletonMapper\ObjectIdentityMap(
            $objectRepositoryFactory, $classMetadataFactory
        );

        // user class metadata
        $userClassMetadata = new SkeletonMapper\Mapping\ClassMetadata($this->testClassName);
        $userClassMetadata->identifier = array('_id');
        $userClassMetadata->identifierFieldNames = array('id');
        $userClassMetadata->mapField(array(
            'name' => '_id',
            'fieldName' => 'id',
        ));
        $userClassMetadata->mapField(array(
            'fieldName' => 'username',
        ));
        $userClassMetadata->mapField(array(
            'fieldName' => 'password',
        ));

        $classMetadataFactory->setMetadataFor($this->testClassName, $userClassMetadata);

        $this->objectManager = new SkeletonMapper\ObjectManager(
            $objectRepositoryFactory,
            $objectPersisterFactory,
            $objectIdentityMap,
            $classMetadataFactory,
            $eventManager
        );

        // user data repo
        $userDataRepository = new UserDataRepository($this->users);

        // user hydrator
        $userHydrator = new UserHydrator();

        // user repo
        $userRepository = new UserRepository(
            $this->objectManager,
            $userDataRepository,
            $objectFactory,
            $userHydrator,
            $eventManager
        );
        $objectRepositoryFactory->addObjectRepository($this->testClassName, $userRepository);

        // user persister
        $userPersister = new UserPersister($this->users);
        $objectPersisterFactory->addObjectPersister($this->testClassName, $userPersister);
    }
}
