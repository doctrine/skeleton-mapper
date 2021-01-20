<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

class ClassMetadataInstantiator implements ClassMetadataInstantiatorInterface
{
    /**
     * @param class-string $className
     */
    public function instantiate(string $className): ClassMetadata
    {
        return new ClassMetadata($className);
    }
}
