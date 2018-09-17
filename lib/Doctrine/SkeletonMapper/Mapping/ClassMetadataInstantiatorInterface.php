<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

interface ClassMetadataInstantiatorInterface
{
    public function instantiate(string $className) : ClassMetadata;
}
