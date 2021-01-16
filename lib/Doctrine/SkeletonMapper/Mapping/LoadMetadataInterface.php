<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

interface LoadMetadataInterface
{
    public static function loadMetadata(ClassMetadataInterface $metadata): void;
}
