<?php

namespace Doctrine\SkeletonMapper\Mapping;

interface LoadMetadataInterface
{
    /**
     * @param \Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface $metadata
     */
    public static function loadMetadata(ClassMetadataInterface $metadata);
}
