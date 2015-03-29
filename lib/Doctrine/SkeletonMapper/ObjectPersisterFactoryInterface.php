<?php

namespace Doctrine\SkeletonMapper;

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
