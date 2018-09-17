<?php

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\DBAL\Connection;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

class DBALObjectPersister extends BasicObjectPersister
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

    public function persistObject($object)
    {
        $data = $this->preparePersistChangeSet($object);

        $this->connection->insert($this->getTableName(), $data);

        $class = $this->objectManager->getClassMetadata(get_class($object));

        if (!isset($data[$class->identifier[0]])) {
            $data[$class->identifier[0]] = $this->connection->lastInsertId();
        }

        return $data;
    }

    public function updateObject($object, ChangeSet $changeSet)
    {
        $data = $this->prepareUpdateChangeSet($object, $changeSet);

        $this->connection->update(
            $this->getTableName(),
            $data,
            $this->getObjectIdentifier($object)
        );

        return $data;
    }

    public function removeObject($object)
    {
        $this->connection->delete(
            $this->getTableName(),
            $this->getObjectIdentifier($object)
        );
    }

    /**
     * @param object $object
     *
     * @return array $identifier
     */
    protected function getObjectIdentifier($object)
    {
        return $this->objectManager
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }
}
