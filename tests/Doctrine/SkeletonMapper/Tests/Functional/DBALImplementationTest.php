<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\DBAL;
use Doctrine\SkeletonMapper\Tests\Model\Profile;
use Doctrine\SkeletonMapper\Tests\Model\User;
use Doctrine\SkeletonMapper\Tests\DBALImplementation\ObjectDataRepository;
use Doctrine\SkeletonMapper\Tests\DBALImplementation\ObjectPersister;
use Doctrine\SkeletonMapper\Tests\UsersTesterInterface;

class DBALImplementationTest extends BaseImplementationTest
{
    protected function setUpImplementation()
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

        $usersTable = $schema->createTable('users');
        $usersTable->addColumn('_id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $usersTable->addColumn('username', 'string', array('length' => 32, 'notnull' => false));
        $usersTable->addColumn('password', 'string', array('length' => 32, 'notnull' => false));
        $usersTable->addColumn('profileId', 'string', array('length' => 32, 'notnull' => false));
        $usersTable->setPrimaryKey(array('_id'));

        $profilesTable = $schema->createTable('profiles');
        $profilesTable->addColumn('_id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $profilesTable->addColumn('name', 'string', array('length' => 32, 'notnull' => false));
        $profilesTable->setPrimaryKey(array('_id'));

        $connection->getSchemaManager()->dropAndCreateDatabase('skeleton_mapper');

        $connectionParams = array(
            'dbname' => 'skeleton_mapper',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );
        $this->connection = DBAL\DriverManager::getConnection($connectionParams, $config);
        $this->connection->getSchemaManager()->createTable($usersTable);
        $this->connection->getSchemaManager()->createTable($profilesTable);

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
            $this->connection->insert('users', $user);
        }

        $this->usersTester = new DBALUsersTester($this->connection);
        $this->profilesTester = new DBALProfilesTester($this->connection);
    }

    protected function createUserDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->connection, $this->userClassName, 'users'
        );
    }

    protected function createUserPersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->connection, $this->userClassName, 'users'
        );
    }

    protected function createProfileDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->connection, $this->profileClassName, 'profiles'
        );
    }

    protected function createProfilePersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->connection, $this->profileClassName, 'profiles'
        );
    }

    public function testSelectProfileData()
    {
        $user = $this->objectManager->find($this->userClassName, 1);

        $profile = new Profile();
        $profile->setName('Jonathan H. Wage');
        $user->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->flush();
        $this->objectManager->clear();

        $userRepository = $this->objectManager->getRepository($this->userClassName);

        $user = $userRepository->findUserWithProfileData(1);
        $this->assertEquals('Jonathan H. Wage', $user->getProfile()->getName());

        $profile = $this->objectManager->find($this->profileClassName, 1);
        $this->assertSame($profile, $user->getProfile());
    }
}

class DBALUsersTester implements UsersTesterInterface
{
    private $connection;

    public function __construct(DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find($id)
    {
        return $this->connection
            ->executeQuery('SELECT * FROM users WHERE _id = ?', array($id))
            ->fetch();
    }

    public function set($id, $key, $value)
    {
        $this->connection->executeQuery(
            sprintf('UPDATE users SET %s = ? WHERE _id = ?', $key),
            array($value, $id)
        );
    }

    public function count()
    {
        return $this->connection->fetchColumn('SELECT COUNT(1) FROM users');
    }
}

class DBALProfilesTester implements UsersTesterInterface
{
    private $connection;

    public function __construct(DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find($id)
    {
        return $this->connection
            ->executeQuery('SELECT * FROM profiles WHERE _id = ?', array($id))
            ->fetch();
    }

    public function set($id, $key, $value)
    {
        $this->connection->executeQuery(
            sprintf('UPDATE profiles SET %s = ? WHERE _id = ?', $key),
            array($value, $id)
        );
    }

    public function count()
    {
        return $this->connection->fetchColumn('SELECT COUNT(1) FROM profiles');
    }
}
