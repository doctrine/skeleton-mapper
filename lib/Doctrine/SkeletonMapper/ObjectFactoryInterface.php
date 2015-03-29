<?php

namespace Doctrine\SkeletonMapper;

interface ObjectFactoryInterface
{
    /**
     * @param string $className
     *
     * @return object
     */
    public function create($className);
}
