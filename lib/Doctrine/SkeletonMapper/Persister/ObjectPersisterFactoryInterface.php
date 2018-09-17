<?php

namespace Doctrine\SkeletonMapper\Persister;

/**
 * Class responsible for retrieving ObjectPersister instances.
 */
interface ObjectPersisterFactoryInterface
{
    /**
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectPersisterInterface
     */
    public function getPersister($className);

    /**
     * @return array
     */
    public function getPersisters();
}
