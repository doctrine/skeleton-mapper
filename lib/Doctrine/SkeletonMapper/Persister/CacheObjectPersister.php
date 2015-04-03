<?php

namespace Doctrine\SkeletonMapper\Persister;

use Doctrine\Common\Cache\Cache;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

class CacheObjectPersister extends BasicObjectPersister
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\Common\Cache\Cache                    $cache
     * @param string                                          $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Cache $cache,
        $className = null)
    {
        parent::__construct($objectManager, $className);
        $this->cache = $cache;
    }

    public function persistObject($object)
    {
        $data = $this->preparePersistChangeSet($object);

        $class = $this->getClassMetadata();

        $this->cache->save($data[$class->identifier[0]], $data);

        return $data;
    }

    public function updateObject($object, array $changeSet)
    {
        $changeSet = $this->prepareUpdateChangeSet($object, $changeSet);

        $class = $this->getClassMetadata();
        $identifier = $this->getObjectIdentifier($object);
        $identifier = $identifier[$class->identifier[0]];

        $objectData = $this->cache->fetch($identifier);

        foreach ($changeSet as $key => $value) {
            $objectData[$key] = $value;
        }

        $this->cache->save($identifier, $objectData);

        return $changeSet;
    }

    public function removeObject($object)
    {
        $class = $this->getClassMetadata();
        $identifier = $this->getObjectIdentifier($object);
        $identifier = $identifier[$class->identifier[0]];

        $this->cache->delete($identifier);
    }
}
