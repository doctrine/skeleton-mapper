<?php

namespace Doctrine\SkeletonMapper\Mapping;

interface ClassMetadataInstantiatorInterface
{
    /**
     * @param string $className
     *
     * @return ClassMetadata
     */
    public function instantiate($className);
}
