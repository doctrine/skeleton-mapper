<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

class ClassMetadataInstantiator implements ClassMetadataInstantiatorInterface
{
    public function instantiate(string $className): ClassMetadata
    {
        return new ClassMetadata($className);
    }
}
