<?php

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use MongoCollection;

abstract class MongoDBObjectPersister extends BasicObjectPersister
{
    /**
     * @var \MongoCollection
     */
    protected $mongoCollection;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \MongoCollection                                $mongoCollection
     * @param string                                          $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MongoCollection $mongoCollection,
        $className = null)
    {
        parent::__construct($objectManager, $className);
        $this->mongoCollection = $mongoCollection;
    }

    public function persistObject($object)
    {
        $data = $this->preparePersistChangeSet($object);

        $this->mongoCollection->insert($data);

        return $data;
    }

    public function updateObject($object, ChangeSet $changeSet)
    {
        $data = $this->prepareUpdateChangeSet($object, $changeSet);

        unset($data['_id']);

        $this->mongoCollection->update(
            $this->getObjectIdentifier($object),
            array('$set' => $data)
        );

        return $data;
    }

    public function removeObject($object)
    {
        $this->mongoCollection->remove($this->getObjectIdentifier($object));
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
