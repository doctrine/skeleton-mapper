<?php

namespace Doctrine\SkeletonMapper\Tests\CacheImplementation;

use Doctrine\SkeletonMapper\Persister\CacheObjectPersister;

class ObjectPersister extends CacheObjectPersister
{
    public function persistObject($object)
    {
        $data = $this->preparePersistChangeSet($object);

        $class = $this->getClassMetadata();

        if (!isset($data[$class->identifier[0]])) {
            $data[$class->identifier[0]] = $this->generateNextId();
        }

        $identifier = $data[$class->identifier[0]];

        $this->cache->save('incrementedId', $identifier);
        $this->cache->save('numObjects', $this->cache->fetch('numObjects') + 1);

        $objectIds = $this->cache->fetch('objectIds') ?: array();
        $objectIds[] = $identifier;
        $this->cache->save('objectIds', $objectIds);

        $this->cache->save($identifier, $data);

        return $data;
    }

    public function removeObject($object)
    {
        parent::removeObject($object);

        $this->cache->save('numObjects', $this->cache->fetch('numObjects') - 1);

        $objectIds = $this->cache->fetch('objectIds') ?: array();
        $key = array_search($object->getId(), $objectIds);
        unset($objectIds[$key]);

        $this->cache->save('objectIds', $objectIds);
    }

    private function generateNextId()
    {
        return $this->cache->fetch('incrementedId') + 1;

    }
}
