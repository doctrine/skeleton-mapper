<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\Tests\Model\User;
use Doctrine\SkeletonMapper\Tests\Model\UserHydrator;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserPersister;

class TestImplementationTest extends BaseImplementationTest
{
    protected function setUp()
    {
        $this->users = new ArrayCollection(array(
            1 => array(
                '_id' => 1,
                'username' => 'jwage',
                'password' => 'password',
            ),
            2 => array(
                '_id' => 2,
                'username' => 'romanb',
                'password' => 'password',
            ),
        ));

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

        $eventManager = new EventManager();
        foreach ($events as $event) {
            $eventManager->addEventListener($event, $this->eventTester);
        }

        $basicObjectHydrator = new SkeletonMapper\Hydrator\BasicObjectHydrator();
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

        foreach ($events as $event) {
            $userClassMetadata->addLifecycleCallback($event, $event);
        }

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

        // user repo
        $userRepository = new UserRepository(
            $this->objectManager,
            $userDataRepository,
            $objectFactory,
            $basicObjectHydrator,
            $eventManager
        );
        $objectRepositoryFactory->addObjectRepository($this->testClassName, $userRepository);

        // user persister
        $userPersister = new UserPersister($this->users);
        $objectPersisterFactory->addObjectPersister($this->testClassName, $userPersister);
    }
}
