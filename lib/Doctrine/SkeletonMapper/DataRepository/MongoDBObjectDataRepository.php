<?php

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use MongoCollection;

abstract class MongoDBObjectDataRepository extends BasicObjectDataRepository
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    protected $objectManager;

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

    /**
     * @return \MongoCollection
     */
    public function getMongoCollection()
    {
        return $this->mongoCollection;
    }

    public function findAll()
    {
        return iterator_to_array($this->mongoCollection->find(array()));
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $cursor = $this->mongoCollection->find($criteria);

        if ($orderBy !== null) {
            $cursor->sort($orderBy);
        }

        if ($limit !== null) {
            $cursor->limit($limit);
        }

        if ($offset !== null) {
            $cursor->skip($offset);
        }

        return iterator_to_array($cursor);
    }

    public function findOneBy(array $criteria)
    {
        return $this->mongoCollection->findOne($criteria);
    }
}
