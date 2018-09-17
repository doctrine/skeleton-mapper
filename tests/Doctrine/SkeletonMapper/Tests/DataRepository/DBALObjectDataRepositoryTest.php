<?php

namespace Doctrine\SkeletonMapper\Tests\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\DataRepository\DBALObjectDataRepository;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class DBALObjectDataRepositoryTest extends TestCase
{
    private $objectManager;
    private $connection;
    private $objectDataRepository;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectManagerInterface')
            ->getMock();

        $this->connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectDataRepository = new TestDBALObjectDataRepository(
            $this->objectManager,
            $this->connection,
            'TestClassName',
            'table_name'
        );
    }

    public function testGetConnection()
    {
        $this->assertSame(
            $this->connection,
            $this->objectDataRepository->getConnection()
        );
    }

    public function testGetTableName()
    {
        $this->assertSame(
            'table_name',
            $this->objectDataRepository->getTableName()
        );
    }

    public function testFindAll()
    {
        $results = array(
            array('username' => 'jwage'),
        );

        $this->connection->expects($this->once())
            ->method('fetchAll')
            ->with('SELECT * FROM table_name')
            ->will($this->returnValue($results));

        $this->assertSame($results, $this->objectDataRepository->findAll());
    }

    public function testFindBy()
    {
        $criteria = array('username' => 'jwage');
        $orderBy = array('username' => 'desc');
        $limit = 20;
        $offset = 20;

        $results = array(
            array('username' => 'jwage'),
        );

        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection->expects($this->any())
            ->method('executeQuery')
            ->with('SELECT * FROM table_name WHERE username = :username ORDER BY username desc LIMIT 20 OFFSET 20')
            ->will($this->returnValue($statement));

        $statement->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue($results));

        $this->assertSame($results, $this->objectDataRepository->findBy($criteria, $orderBy, $limit, $offset));
    }

    public function testFindOneBy()
    {
        $criteria = array('username' => 'jwage');

        $results = array(
            array('username' => 'jwage'),
        );

        $statement = $this->getMockBuilder('Doctrine\DBAL\Statement')
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection->expects($this->any())
            ->method('executeQuery')
            ->with('SELECT * FROM table_name WHERE username = :username')
            ->will($this->returnValue($statement));

        $statement->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue($results));

        $this->assertSame($results[0], $this->objectDataRepository->findOneBy($criteria));
    }
}

class TestDBALObjectDataRepository extends DBALObjectDataRepository
{
}
