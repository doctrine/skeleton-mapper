<?php

namespace Doctrine\SkeletonMapper\Mapping;

/**
 * @author Igor Timoshenko <igor.timoshenko@i.ua>
 */
interface ClassMetadataInstantiatorInterface
{
    /**
     * @param string $className
     *
     * @return ClassMetadata
     */
    public function instantiate($className);
}
