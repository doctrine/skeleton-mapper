<?php

namespace Doctrine\SkeletonMapper\Tests\MongoDBImplementation\User;

use Doctrine\SkeletonMapper\Repository\ObjectDataRepository;
use MongoCollection;

class UserDataRepository extends ObjectDataRepository
{
    private $mongoCollection;

    public function __construct(MongoCollection $mongoCollection)
    {
        $this->mongoCollection = $mongoCollection;
    }

    public function find($id)
    {
        return $this->mongoCollection->findOne(array('_id' => $id));
    }

    public function findByObject($object)
    {
        return $this->find($object->id);
    }

    public function findAll()
    {
        return $this->mongoCollection->find(array());
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
