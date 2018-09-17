<?php

namespace Doctrine\SkeletonMapper\Persister;

/**
 * Class responsible for retrieving ObjectPersister instances.
 *
 * @author Magnus Nordlander <magnus@fervo.se>
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
