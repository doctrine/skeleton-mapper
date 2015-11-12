<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL;
use Doctrine\DBAL\Driver\PDOException;
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
    /**
     * @var boolean Whether the database schema is initialized.
     */
    private static $initialized = false;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Gets a <b>real</b> database connection using the following parameters
     * of the $GLOBALS array:
     *
     * 'db_type' : The name of the Doctrine DBAL database driver to use.
     * 'db_username' : The username to use for connecting.
     * 'db_password' : The password to use for connecting.
     * 'db_host' : The hostname of the database to connect to.
     * 'db_server' : The server name of the database to connect to
     *               (optional, some vendors allow multiple server instances with different names on the same host).
     * 'db_name' : The name of the database to connect to.
     * 'db_port' : The port of the database to connect to.
     *
     * Usually these variables of the $GLOBALS array are filled by PHPUnit based
     * on an XML configuration file. If no such parameters exist, an SQLite
     * in-memory database is used.
     *
     * IMPORTANT: Each invocation of this method returns a NEW database connection.
     *
     * @return Connection The database connection instance.
     */
    public static function getConnection()
    {
        $conn = DriverManager::getConnection(self::getConnectionParams());

        return $conn;
    }

    private static function getConnectionParams() {
        if (self::hasRequiredConnectionParams()) {
            return self::getSpecifiedConnectionParams();
        }

        return self::getFallbackConnectionParams();
    }

    private static function hasRequiredConnectionParams()
    {
        return isset(
            $GLOBALS['db_type'],
            $GLOBALS['db_username'],
            $GLOBALS['db_password'],
            $GLOBALS['db_host'],
            $GLOBALS['db_name'],
            $GLOBALS['db_port']
        )
        && isset(
            $GLOBALS['tmpdb_type'],
            $GLOBALS['tmpdb_username'],
            $GLOBALS['tmpdb_password'],
            $GLOBALS['tmpdb_host'],
            $GLOBALS['tmpdb_port']
        );
    }
    private static function getSpecifiedConnectionParams() {
        $realDbParams = self::getParamsForMainConnection();
        $tmpDbParams = self::getParamsForTemporaryConnection();

        $realConn = DriverManager::getConnection($realDbParams);

        // Connect to tmpdb in order to drop and create the real test db.
        $tmpConn = DriverManager::getConnection($tmpDbParams);

        $platform  = $tmpConn->getDatabasePlatform();

        if (! self::$initialized) {
            if ($platform->supportsCreateDropDatabase()) {
                $dbname = $realConn->getDatabase();
                $realConn->close();

                $tmpConn->getSchemaManager()->dropAndCreateDatabase($dbname);

                $tmpConn->close();
            } else {
                $sm = $realConn->getSchemaManager();

                $schema = $sm->createSchema();
                $stmts = $schema->toDropSql($realConn->getDatabasePlatform());

                foreach ($stmts as $stmt) {
                    $realConn->exec($stmt);
                }
            }

            self::$initialized = true;
        }

        return $realDbParams;
    }

    private static function getFallbackConnectionParams() {
        $params = array(
            'driver' => 'pdo_sqlite',
            'memory' => true
        );

        if (isset($GLOBALS['db_path'])) {
            $params['path'] = $GLOBALS['db_path'];
            unlink($GLOBALS['db_path']);
        }

        return $params;
    }

    private static function getParamsForTemporaryConnection()
    {
        $connectionParams = array(
            'driver' => $GLOBALS['tmpdb_type'],
            'user' => $GLOBALS['tmpdb_username'],
            'password' => $GLOBALS['tmpdb_password'],
            'host' => $GLOBALS['tmpdb_host'],
            'dbname' => null,
            'port' => $GLOBALS['tmpdb_port']
        );

        if (isset($GLOBALS['tmpdb_name'])) {
            $connectionParams['dbname'] = $GLOBALS['tmpdb_name'];
        }

        if (isset($GLOBALS['tmpdb_server'])) {
            $connectionParams['server'] = $GLOBALS['tmpdb_server'];
        }

        if (isset($GLOBALS['tmpdb_unix_socket'])) {
            $connectionParams['unix_socket'] = $GLOBALS['tmpdb_unix_socket'];
        }

        return $connectionParams;
    }

    private static function getParamsForMainConnection()
    {
        $connectionParams = array(
            'driver' => $GLOBALS['db_type'],
            'user' => $GLOBALS['db_username'],
            'password' => $GLOBALS['db_password'],
            'host' => $GLOBALS['db_host'],
            'dbname' => $GLOBALS['db_name'],
            'port' => $GLOBALS['db_port']
        );

        if (isset($GLOBALS['db_server'])) {
            $connectionParams['server'] = $GLOBALS['db_server'];
        }

        if (isset($GLOBALS['db_unix_socket'])) {
            $connectionParams['unix_socket'] = $GLOBALS['db_unix_socket'];
        }

        return $connectionParams;
    }

    /**
     * @return Connection
     */
    public static function getTempConnection()
    {
        return DriverManager::getConnection(self::getParamsForTemporaryConnection());
    }

    protected function setUpImplementation()
    {
        $this->connection = self::getConnection();

        $schemaManager = $this->connection->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $stmts = $schema->toDropSql($this->connection->getDatabasePlatform());

        foreach ($stmts as $stmt) {
            $this->connection->exec($stmt);
        }

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

        $schemaManager->createTable($usersTable);
        $schemaManager->createTable($profilesTable);
        $schemaManager->createTable($groupsTable);

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
