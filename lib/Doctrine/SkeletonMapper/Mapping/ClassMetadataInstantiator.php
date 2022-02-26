<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

/**
 * @template-implements ClassMetadataInstantiatorInterface<object>
 */
class ClassMetadataInstantiator implements ClassMetadataInstantiatorInterface
{
    public function instantiate(string $className): ClassMetadata
    {
        return new ClassMetadata($className);
    }
}
