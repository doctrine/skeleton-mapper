<?php

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\DBAL\Connection;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Base class for DBAL object data repositories to extend from.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class DBALObjectDataRepository extends BasicObjectDataRepository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\DBAL\Connection                       $connection
     * @param string                                          $className
     * @param string                                          $tableName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Connection $connection,
        $className = null,
        $tableName = null)
    {
        parent::__construct($objectManager, $className);
        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function findAll()
    {
        $sql = sprintf('SELECT * FROM %s', $this->getTableName());

        return $this->connection->fetchAll($sql);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $where = array();
        $params = array();
        foreach ($criteria as $key => $value) {
            $where[] = sprintf('%s = :%s', $key, $key);
            $params[$key] = $value;
        }

        $sqlParts = array();
        $sqlParts[] = sprintf('SELECT * FROM %s WHERE %s', $this->getTableName(), implode(' AND ', $where));

        if ($orderBy !== null) {
            $orderBySqlParts = array();
            foreach ($orderBy as $fieldName => $orientation) {
                $orderBySqlParts[] = sprintf('%s %s', $fieldName, $orientation);
            }

            $sqlParts[] = 'ORDER BY '.implode(', ', $orderBySqlParts);
        }

        if ($limit !== null) {
            $sqlParts[] = sprintf('LIMIT %s', $limit);
        }

        if ($offset !== null) {
            $sqlParts[] = sprintf('OFFSET %s', $offset);
        }

        $sql = implode(' ', $sqlParts);

        return $this->connection
            ->executeQuery($sql, $params)
            ->fetchAll();
    }

    public function findOneBy(array $criteria)
    {
        return current($this->findBy($criteria)) ?: null;
    }
}
