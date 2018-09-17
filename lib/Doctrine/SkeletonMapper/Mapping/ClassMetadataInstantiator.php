<?php

namespace Doctrine\SkeletonMapper\Mapping;

/**
 * @author Igor Timoshenko <igor.timoshenko@i.ua>
 */
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
