<?php

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\Common\Cache\Cache;
use Doctrine\SkeletonMapper\ObjectManagerInterface;

class CacheObjectDataRepository extends BasicObjectDataRepository
{
    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param \Doctrine\Common\Cache\Cache    $cache
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Cache $cache,
        $className = null)
    {
        parent::__construct($objectManager, $className);
        $this->cache = $cache;
    }

    public function find($id)
    {
        if (is_array($id)) {
            $id = current($id);
        }

        return $this->cache->fetch($id) ?: null;
    }

    public function findAll()
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function findBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null)
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function findOneBy(array $criteria)
    {
        throw new \BadMethodCallException('Not implemented.');
    }
}
