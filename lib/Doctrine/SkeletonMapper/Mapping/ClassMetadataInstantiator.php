<?php

namespace Doctrine\SkeletonMapper\Mapping;

class ClassMetadataInstantiator implements ClassMetadataInstantiatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function instantiate($className)
    {
        return new ClassMetadata($className);
    }
}
