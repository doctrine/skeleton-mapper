<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\EventManager;
use Doctrine\DBAL;
use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\Tests\Model\User;
use Doctrine\SkeletonMapper\Tests\Model\UserHydrator;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
use Doctrine\SkeletonMapper\Tests\DBALImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\DBALImplementation\User\UserPersister;

class DBALImplementationTest extends BaseImplementationTest
{
    protected function setUp()
    {
        $config = new DBAL\Configuration();
        $connectionParams = array(
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );
        $connection = DBAL\DriverManager::getConnection($connectionParams, $config);

        $schema = new DBAL\Schema\Schema();
        $table = $schema->createTable('users');
        $table->addColumn('_id', 'integer', array('unsigned' => true));
        $table->addColumn('username', 'string', array('length' => 32, 'notnull' => false));
        $table->addColumn('password', 'string', array('length' => 32, 'notnull' => false));

        $connection->getSchemaManager()->dropAndCreateDatabase('skeleton_mapper');

        $connectionParams = array(
            'dbname' => 'skeleton_mapper',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );
        $connection = DBAL\DriverManager::getConnection($connectionParams, $config);
        $connection->getSchemaManager()->createTable($table);

        $users = array(
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
        );

        foreach ($users as $user) {
            $connection->insert('users', $user);
        }

        $this->users = new UsersTester($connection);

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
        $userDataRepository = new UserDataRepository(
            $this->objectManager, $connection
        );

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
        $userPersister = new UserPersister($this->objectManager, $connection);
        $objectPersisterFactory->addObjectPersister($this->testClassName, $userPersister);
    }
}

class UsersTester
{
    private $connection;

    public function __construct(DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    public function count()
    {
        return $this->connection->fetchColumn('SELECT COUNT(1) FROM users');
    }
}
