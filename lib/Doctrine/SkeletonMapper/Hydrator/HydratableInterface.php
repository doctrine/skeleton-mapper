<?php

namespace Doctrine\SkeletonMapper\Hydrator;

use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Interface hydratable objects must implement.
 */
interface HydratableInterface
{
    /**
     * @param array                                           $data
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     */
    public function hydrate(array $data, ObjectManagerInterface $objectManager);
}
