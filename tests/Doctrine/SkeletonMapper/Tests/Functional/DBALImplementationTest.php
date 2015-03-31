<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\DBAL;
use Doctrine\SkeletonMapper\Tests\Model\User;
use Doctrine\SkeletonMapper\Tests\Model\UserRepository;
use Doctrine\SkeletonMapper\Tests\DBALImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\DBALImplementation\User\UserPersister;
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
        $table = $schema->createTable('users');
        $table->addColumn('_id', 'integer', array('unsigned' => true, 'autoincrement' => true));
        $table->addColumn('username', 'string', array('length' => 32, 'notnull' => false));
        $table->addColumn('password', 'string', array('length' => 32, 'notnull' => false));
        $table->setPrimaryKey(array('_id'));

        $connection->getSchemaManager()->dropAndCreateDatabase('skeleton_mapper');

        $connectionParams = array(
            'dbname' => 'skeleton_mapper',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );
        $this->connection = DBAL\DriverManager::getConnection($connectionParams, $config);
        $this->connection->getSchemaManager()->createTable($table);

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
    }

    protected function createUserDataRepository()
    {
        return new UserDataRepository(
            $this->objectManager, $this->connection
        );
    }

    protected function createUserRepository()
    {
        return new UserRepository(
            $this->objectManager,
            $this->userDataRepository,
            $this->objectFactory,
            $this->basicObjectHydrator,
            $this->eventManager
        );
    }

    protected function createUserPersister()
    {
        return new UserPersister(
            $this->objectManager, $this->connection
        );
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
