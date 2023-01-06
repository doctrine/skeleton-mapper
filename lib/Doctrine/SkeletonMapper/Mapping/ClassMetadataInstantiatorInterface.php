<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

/** @template T of object */
interface ClassMetadataInstantiatorInterface
{
    /**
     * @param class-string<T> $className
     *
     * @return ClassMetadata<T>
     */
    public function instantiate(string $className): ClassMetadata;
}
