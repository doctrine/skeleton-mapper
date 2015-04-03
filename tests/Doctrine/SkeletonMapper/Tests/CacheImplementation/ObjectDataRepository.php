<?php

namespace Doctrine\SkeletonMapper\Tests\CacheImplementation;

use Doctrine\SkeletonMapper\DataRepository\CacheObjectDataRepository;

class ObjectDataRepository extends CacheObjectDataRepository
{
    public function findAll()
    {
        $objectIds = $this->cache->fetch('objectIds') ?: array();

        $objects = array();
        foreach ($objectIds as $objectId) {
            $objects[] = $this->cache->fetch($objectId);
        }

        return $objects;
    }

    public function findBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null)
    {
        $objects = array();

        foreach ($this->findAll() as $object) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($object[$key] !== $value) {
                    $matches = false;
                }
            }

            if ($matches) {
                $objects[] = $object;
            }
        }

        return $objects;
    }

    public function findOneBy(array $criteria)
    {
        foreach ($this->findAll() as $object) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($object[$key] !== $value) {
                    $matches = false;
                }
            }

            if ($matches) {
                return $object;
            }
        }
    }
}
