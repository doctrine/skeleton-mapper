<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\DBAL;
use Doctrine\SkeletonMapper\Tests\Model\Profile;
use Doctrine\SkeletonMapper\Tests\Model\User;
use Doctrine\SkeletonMapper\Tests\DBALImplementation\ObjectDataRepository;
use Doctrine\SkeletonMapper\Tests\DBALImplementation\ObjectPersister;
use Doctrine\SkeletonMapper\Tests\DataTesterInterface;

/**
 * @group functional
 */
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
        $usersTable->addColumn('groupIds', 'string', array('length' => 32, 'notnull' => false));
        $usersTable->setPrimaryKey(array('_id'));

        $profilesTable = $schema->createTable('profiles');
        $profilesTable->addColumn('_id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $profilesTable->addColumn('name', 'string', array('length' => 32, 'notnull' => false));
        $profilesTable->addColumn('address1', 'string', array('length' => 32, 'notnull' => false));
        $profilesTable->addColumn('address2', 'string', array('length' => 32, 'notnull' => false));
        $profilesTable->addColumn('city', 'string', array('length' => 32, 'notnull' => false));
        $profilesTable->addColumn('state', 'string', array('length' => 32, 'notnull' => false));
        $profilesTable->addColumn('zip', 'string', array('length' => 32, 'notnull' => false));
        $profilesTable->setPrimaryKey(array('_id'));

        $groupsTable = $schema->createTable('groups');
        $groupsTable->addColumn('_id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $groupsTable->addColumn('name', 'string', array('length' => 32, 'notnull' => false));
        $groupsTable->setPrimaryKey(array('_id'));

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
        $this->connection->getSchemaManager()->createTable($groupsTable);

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

        $this->usersTester = new DBALTester($this->connection, 'users');
        $this->profilesTester = new DBALTester($this->connection, 'profiles');
        $this->groupsTester = new DBALTester($this->connection, 'groups');
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

    protected function createGroupDataRepository()
    {
        return new ObjectDataRepository(
            $this->objectManager, $this->connection, $this->groupClassName, 'groups'
        );
    }

    protected function createGroupPersister()
    {
        return new ObjectPersister(
            $this->objectManager, $this->connection, $this->groupClassName, 'groups'
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

class DBALTester implements DataTesterInterface
{
    private $connection;
    private $tableName;

    public function __construct(DBAL\Connection $connection, $tableName)
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    public function find($id)
    {
        return $this->connection
            ->executeQuery(sprintf('SELECT * FROM %s WHERE _id = ?', $this->tableName), array($id))
            ->fetch();
    }

    public function set($id, $key, $value)
    {
        $this->connection->executeQuery(
            sprintf('UPDATE %s SET %s = ? WHERE _id = ?', $this->tableName, $key),
            array($value, $id)
        );
    }

    public function count()
    {
        return $this->connection->fetchColumn(sprintf('SELECT COUNT(1) FROM %s', $this->tableName));
    }
}
