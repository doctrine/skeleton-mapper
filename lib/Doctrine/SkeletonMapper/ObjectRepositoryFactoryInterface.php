<?php

namespace Doctrine\SkeletonMapper;

interface ObjectRepositoryFactoryInterface
{
    /**
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className);
}
