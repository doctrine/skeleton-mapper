<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

interface LoadMetadataInterface
{
    /** @psalm-param ClassMetadataInterface<object> $metadata */
    public static function loadMetadata(ClassMetadataInterface $metadata): void;
}
